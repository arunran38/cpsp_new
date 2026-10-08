import re

file_path = "app/Http/Controllers/PetitionForwardingController.php"
with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

content = re.sub(r'\s*&&\s*!Auth::user\(\)->canAccess\(\'access admin dashboard\'\)', '', content)
content = re.sub(r'\s*\|\|\s*!Auth::user\(\)->canAccess\(\'access admin dashboard\'\)', '', content)
content = re.sub(r'\s*&&\s*Auth::user\(\)->canAccess\(\'access admin dashboard\'\)', '', content)
content = re.sub(r'\s*\|\|\s*Auth::user\(\)->canAccess\(\'access admin dashboard\'\)', '', content)
content = re.sub(r'canAccess\(\'access admin dashboard\'\)', r"canAccess('view all petitions')", content)

with open(file_path, 'w', encoding='utf-8') as f:
    f.write(content)
