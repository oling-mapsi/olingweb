#!/usr/bin/env python3
"""Insert only AI contexts; retain every existing production protection verbatim."""
import sys,textwrap
from pathlib import Path
p=Path(sys.argv[1]);s=p.read_text();block=textwrap.indent(Path(__file__).with_name('ai-firewalls.yaml').read_text(),'        ')
if block in s: raise SystemExit(0)
assert all(x not in s for x in ['        ai:', '        ai_tracking:', '        ai_relay:']), 'Unexpected AI firewall; inspect before replacing'
assert s.count('    firewalls:\n')==1
p.write_text(s.replace('    firewalls:\n','    firewalls:\n'+block,1))
