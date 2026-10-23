#!/usr/bin/env python3
"""Server-only: bind crm.oling.fr after its A record and verified release backup exist."""
import fcntl, os, socket, subprocess, sys
from pathlib import Path
assert os.geteuid()==0
backup=Path(sys.argv[1]).resolve()
assert backup.parent==Path('/var/backups/oling') and (backup/'offsite-verified').is_file()
assert '54.37.230.240' in socket.gethostbyname_ex('crm.oling.fr')[2]
lock=open('/var/lock/oling-deploy-cms-p0.lock','a');fcntl.flock(lock,fcntl.LOCK_EX|fcntl.LOCK_NB)
site=Path('/etc/apache2/sites-available/crm.oling.fr.conf')
assert not site.exists(), 'Domain already configured: inspect rather than overwrite.'
assert Path('/var/www/oling-current/public/index.php').exists()
def run(*args): subprocess.run(args,check=True)
webroot=Path('/var/www/letsencrypt');webroot.mkdir(exist_ok=True,mode=0o755)
common='''    Include /var/www/releases/oling-security-20261022/config/apache/security-headers.conf
    ServerName crm.oling.fr
    ServerAdmin contact@oling.fr
    ErrorLog ${APACHE_LOG_DIR}/crm_error.log
    CustomLog ${APACHE_LOG_DIR}/crm_access.log combined
'''
http='''<VirtualHost *:80>
'''+common+'''    DocumentRoot /var/www/letsencrypt
    <Directory /var/www/letsencrypt>
        AllowOverride None
        Options -Indexes
        Require all granted
    </Directory>
    RewriteEngine On
    RewriteCond %{REQUEST_URI} !^/\\.well-known/acme-challenge/
    RewriteRule ^ https://crm.oling.fr%{REQUEST_URI} [R=301,L,NE]
</VirtualHost>
'''
https='''<VirtualHost *:443>
'''+common+'''    DocumentRoot /var/www/oling-current/public
    <Directory /var/www/oling-current/public>
        AllowOverride All
        Require all granted
        Options FollowSymLinks
        DirectoryIndex index.php
    </Directory>
    Include /etc/letsencrypt/options-ssl-apache.conf
    SSLCertificateFile /etc/letsencrypt/live/crm.oling.fr/fullchain.pem
    SSLCertificateKeyFile /etc/letsencrypt/live/crm.oling.fr/privkey.pem
    Header always set X-Robots-Tag "noindex, nofollow"
    RewriteEngine On
    # The existing login returns /admin; on this host it opens the CRM.
    RewriteRule ^/(?:admin/?)?$ /admin/crm [R=302,L]
</VirtualHost>
'''
(backup/'crm-domain-before.txt').write_text('No CRM vhost. DNS A crm -> 54.37.230.240 newly added in OVH.\n')
try:
    site.write_text(http);run('a2ensite',site.name);run('apache2ctl','configtest');run('systemctl','reload','apache2')
    accounts=list(Path('/etc/letsencrypt/accounts/acme-v02.api.letsencrypt.org/directory').iterdir())
    assert len(accounts)==1, 'Choose existing ACME account explicitly.'
    run('certbot','certonly','--webroot','-w',str(webroot),'-d','crm.oling.fr','--cert-name','crm.oling.fr','--non-interactive','--account',accounts[0].name,'--deploy-hook','systemctl reload apache2')
    site.write_text(http+https);run('apache2ctl','configtest');run('systemctl','reload','apache2')
    for path in ['/','/login','/admin/crm']:
        run('curl','--fail','--silent','--show-error','--output','/dev/null','https://crm.oling.fr'+path)
    (backup/'crm-domain-installed.conf').write_text(site.read_text())
    print('CRM HTTPS configured; original OLING vhost unchanged.')
except BaseException:
    run('a2dissite',site.name);run('apache2ctl','configtest');run('systemctl','reload','apache2')
    raise
