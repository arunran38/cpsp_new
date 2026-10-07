import os
import glob

replacements = [
    (
        "'CV' => 'Confidential Verification (CV)',",
        "'CV' => 'Confidential Verification (CV)',\n                            'IV' => 'Internal Vigilance (IV)',"
    ),
    (
        "'CV' => 'CV',",
        "'CV' => 'CV',\n                        'IV' => 'IV',"
    ),
    (
        "<option value=\"CV\">Confidential Verification (CV)</option>",
        "<option value=\"CV\">Confidential Verification (CV)</option>\n                                                    <option value=\"IV\">Internal Vigilance (IV)</option>"
    ),
    (
        "<option value=\"CV\" {{ request('status') == 'CV' ? 'selected' : '' }} class=\"font-medium text-slate-800 bg-white py-1\">Confidential Verification (CV)</option>",
        "<option value=\"CV\" {{ request('status') == 'CV' ? 'selected' : '' }} class=\"font-medium text-slate-800 bg-white py-1\">Confidential Verification (CV)</option>\n                                    <option value=\"IV\" {{ request('status') == 'IV' ? 'selected' : '' }} class=\"font-medium text-slate-800 bg-white py-1\">Internal Vigilance (IV)</option>"
    ),
    (
        "'CV' => 'Confidential Verification (CV)'",
        "'CV' => 'Confidential Verification (CV)', 'IV' => 'Internal Vigilance (IV)'"
    ),
    (
        "['value' => 'CV', 'label' => 'Confidential Verification (CV)', 'count' => $pendingCounts['CV']],",
        "['value' => 'CV', 'label' => 'Confidential Verification (CV)', 'count' => $pendingCounts['CV']],\n                                ['value' => 'IV', 'label' => 'Internal Vigilance (IV)', 'count' => $pendingCounts['IV']],"
    ),
    (
        "filterDecision === 'CV' ? 'Confidential Verification (CV)' :",
        "filterDecision === 'CV' ? 'Confidential Verification (CV)' :\n                                filterDecision === 'IV' ? 'Internal Vigilance (IV)' :"
    )
]

for root, _, files in os.walk('resources/views'):
    for file in files:
        if file.endswith('.blade.php'):
            path = os.path.join(root, file)
            with open(path, 'r', encoding='utf-8') as f:
                content = f.read()
            original_content = content
            for old, new in replacements:
                content = content.replace(old, new)
            if content != original_content:
                with open(path, 'w', encoding='utf-8') as f:
                    f.write(content)
                print(f"Updated {path}")

# Also update Decision.php
decision_path = "app/Models/Decision.php"
if os.path.exists(decision_path):
    with open(decision_path, 'r', encoding='utf-8') as f:
        content = f.read()
    content = content.replace("'CV' => 'Confidential Verification (CV)',", "'CV' => 'Confidential Verification (CV)',\n            'IV' => 'Internal Vigilance (IV)',")
    with open(decision_path, 'w', encoding='utf-8') as f:
        f.write(content)
    print(f"Updated {decision_path}")
