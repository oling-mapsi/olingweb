#!/usr/bin/env python3
"""Insert only the CRM firewall; retain the production main firewall verbatim."""
import sys
from pathlib import Path

target = Path(sys.argv[1])
source = target.read_text()
block = Path(__file__).with_name('crm-firewall.yaml').read_text()
if block in source:
    raise SystemExit(0)
assert '        crm:' not in source, 'Unexpected existing CRM firewall; inspect before replacing'
assert source.count('    firewalls:\n') == 1
target.write_text(source.replace('    firewalls:\n', '    firewalls:\n' + block, 1))
