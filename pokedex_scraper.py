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

        # Extract name
        name = card.find('a', class_='ent-name').text

        # Extract types
        types = [t.text for t in card.find_all('a', class_='itype')]

        # Extract dex number
        dex_number = card.find('span', class_='infocard-lg-data text-muted').text.strip()

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

        # Combine fields into a single text document
        text = f"Dex Number: {dex_number} Name: {name} Type: {', '.join(types)} Description: {description}"
        pokemons.append({'docno': int(idx + 1), 'text': text})

    print("Scraping Done...")

    # Create pokedex corpus
    df = pd.DataFrame(pokemons)
    df.to_csv('pokemon_data.csv', index=False)
    print("Corpus prepared!")
else:
    print("Failed to retrieve the page!")