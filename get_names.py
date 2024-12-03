import pandas as pd

# Sample DataFrame
data = pd.read_csv("poke_data\pokemon_full.csv")

# Create a DataFrame
df = pd.DataFrame(data)

# Write Pokémon names to a text file
with open("pokemon_names.txt", "w", encoding="utf-8") as f:
    for name in df['name']:
        f.write(name + '\n')

print("pokemon_names.txt file created successfully!")
