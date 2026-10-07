import sys
import os

def replace_in_file(path, old, new):
    if not os.path.exists(path): return
    with open(path, 'r', encoding='utf-8') as f:
        content = f.read()
    content = content.replace(old, new)
    with open(path, 'w', encoding='utf-8') as f:
        f.write(content)

replace_in_file('resources/views/user/vr_view.blade.php', "route('petitions.reports.vrs')", "route('petitions.reports', ['tab' => 'vrs'])")
replace_in_file('resources/views/user/forwardings_view.blade.php', "route('petitions.reports.forwarded')", "route('petitions.reports', ['tab' => 'forwarded'])")
replace_in_file('resources/views/user/decisions_view.blade.php', "route('petitions.reports.decisions')", "route('petitions.reports', ['tab' => 'decisions'])")
