import os
import re

directories = ['app', 'resources/views', 'routes']
replacements = [
    (r"hasRole\('admin'\)", r"canAccess('access admin dashboard')")
]

for directory in directories:
    for root, _, files in os.walk(directory):
        for file in files:
            if file.endswith('.php') or file.endswith('.blade.php'):
                filepath = os.path.join(root, file)
                with open(filepath, 'r', encoding='utf-8') as f:
                    content = f.read()
                
                original_content = content
                for pattern, replacement in replacements:
                    content = re.sub(pattern, replacement, content)
                
                if content != original_content:
                    with open(filepath, 'w', encoding='utf-8') as f:
                        f.write(content)
                    print(f'Replaced in {filepath}')
