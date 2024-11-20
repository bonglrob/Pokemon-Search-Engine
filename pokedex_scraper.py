import requests
from bs4 import BeautifulSoup
import pandas as pd

POKEMONS_URL = "https://pokemondb.net/pokedex/national"

# Fetch the page
response = requests.get(POKEMONS_URL)
if response.status_code == 200:
    soup = BeautifulSoup(response.text, 'html.parser')

    # Find Pokémon cards
    pokemons = []
    cards = soup.find_all('div', class_='infocard')
    for idx, card in enumerate(cards):
        # if idx == 3:
        #     print('two entry uploaded')
        #     break

        # extract image
        img = card.find('img')
        src = img.get('src')
        alt_text = img.get('alt')

        # Extract name
        name = card.find('a', class_='ent-name').text

        # Extract types
        types = [t.text for t in card.find_all('a', class_='itype')]

        # Extract dex number
        dex_number = card.find('span', class_='infocard-lg-data text-muted').find('small').text.strip()

        # Extract description
        description_url = card.find('a', class_='ent-name')['href']
        description_page = requests.get(f"https://pokemondb.net{description_url}")
        description_soup = BeautifulSoup(description_page.text, 'html.parser')

        description = description_soup.find('td', class_='cell-med-text')
        
        # checks that description exists
        if description:
            description = description.text.strip()
        else:
            description = "Oops! No Pokedex information is available at this time."

        # get all text from the page
        text = description_soup.get_text(separator="\n")

        no_space_text = '\n'.join([line.strip() for line in text.split('\n') if line.strip()])
        no_space_text += f"Dex Number: {dex_number} Name: {name} Type: {', '.join(types)} Description: {description}"

        # add pokemon row entry
        pokemons.append({
            'docno': int(idx + 1), 
            'name': name, 
            'dexno': dex_number, 
            'url': f'https://bulbapedia.bulbagarden.net/wiki/{name}_(Pok%C3%A9mon)', 
            'img': src, 
            'type': ', '.join(types), 
            'description': description, 
            'text': no_space_text
        })

    print("Scraping Done...")

    # Create pokedex corpus
    df = pd.DataFrame(pokemons)
    df.to_csv('pokemon_full.csv', index=False)
    print("Corpus prepared!")
else:
    print("Failed to retrieve the page!")