
import os
import re

directory = r'c:\Users\lenov\OneDrive\Desktop\surveys system\views'

def fix_file(filepath):
    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()
    
    # Check if already fixed
    if '$base_url' in content:
        # Already has some fixes, but let's be careful. 
        # Actually, I already fixed login/register manually.
        pass

    # Replace href="/...
    # Avoid replacing if it already has php echo
    new_content = re.sub(r'(href|src|action)=["\']/(?!<\?)([^"\']*)["\']', r'\1="<?php echo $base_url; ?>/\2"', content)
    
    # Replace fetch('/...
    new_content = re.sub(r'fetch\([\'"]/(?!<\?)([^"\']*)[\'"]', r"fetch('<?php echo $base_url; ?>/\1'", new_content)
    
    if content != new_content:
        print(f"Fixing {filepath}")
        with open(filepath, 'w', encoding='utf-8') as f:
            f.write(new_content)

for root, dirs, files in os.walk(directory):
    for file in files:
        if file.endswith(".php"):
            fix_file(os.path.join(root, file))

# Also fix install.php to redirect to ./index.php/login or similar, but install.php is standalone.
# We will leave install.php alone for now or handle it separately.
