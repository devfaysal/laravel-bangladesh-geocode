from bs4 import BeautifulSoup

wiki_path = '/home/faysal/.gemini/antigravity-ide/brain/b0ad5ee3-6852-461d-bf84-3718b32d7464/.system_generated/steps/654/content.md'

with open(wiki_path, 'r', encoding='utf-8') as f:
    html = f.read()

soup = BeautifulSoup(html, 'html.parser')
heading = soup.find(lambda tag: tag.name in ['h3', 'h4'] and 'ঢাকা জেলা' in tag.get_text())

if heading:
    print(f"Heading: {heading.name}")
    # Let's walk up to section or parent
    parent = heading.parent
    print(f"Parent: {parent.name}, class: {parent.get('class')}")
    
    # Siblings of parent
    sibling = parent
    for i in range(5):
        sibling = sibling.next_sibling
        if not sibling:
            break
        name = sibling.name if sibling.name else 'TextNode'
        snippet = sibling.get_text(strip=True)[:100] if sibling.name else str(sibling).strip()[:100]
        print(f"  Parent Sibling {i}: name={name}, snippet={snippet}")
        
    # Is there a section parent?
    section = heading.find_parent('section')
    if section:
        print(f"Section parent: {section.name}, id: {section.get('id')}")
        tables = section.find_all('table')
        print(f"  Tables inside section: {len(tables)}")
