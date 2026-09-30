import os

directories = ['app', 'resources/views', 'routes']
for directory in directories:
    for root, _, files in os.walk(directory):
        for file in files:
            if file.endswith('.php') or file.endswith('.blade.php'):
                filepath = os.path.join(root, file)
                with open(filepath, 'r', encoding='utf-8') as f:
                    content = f.read()
                new_content = content.replace(r"\'admin\'", "'admin'")
                if new_content != content:
                    with open(filepath, 'w', encoding='utf-8') as f:
                        f.write(new_content)
                    print(f'Fixed {filepath}')
