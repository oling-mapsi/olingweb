#!/usr/bin/env python3
from pathlib import Path
import sys
path=Path(sys.argv[1]);s=path.read_text()
link='<a class="nav-link" href="{{ path(\'admin_crm\') }}">CRM commercial</a>\n'
if "path('admin_crm')" not in s:
 if '</nav>' in s: s=s.replace('</nav>',link+'</nav>',1)
 elif '<div class="nav nav-pills flex-wrap gap-2">' in s: s=s.replace('<div class="nav nav-pills flex-wrap gap-2">','<div class="nav nav-pills flex-wrap gap-2">\n'+link,1)
 else: raise SystemExit('Navigation inconnue : adaptation requise')
 path.write_text(s)
