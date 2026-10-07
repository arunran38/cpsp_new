import os
path = 'app/Http/Controllers/PetitionController.php'
with open(path, 'r', encoding='utf-8') as f:
    content = f.read()
content = content.replace("$request->get(", "$request->input(")
with open(path, 'w', encoding='utf-8') as f:
    f.write(content)

path2 = 'database/seeders/RolesAndPermissionsSeeder.php'
with open(path2, 'r', encoding='utf-8') as f:
    content = f.read()
content = content.replace(r"\Spatie\Permission\Models\Permission", "Permission")
with open(path2, 'w', encoding='utf-8') as f:
    f.write(content)
