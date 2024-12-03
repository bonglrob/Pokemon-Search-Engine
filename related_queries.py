import sys
import difflib


with open("pokemon_names.txt", "r", encoding="utf-8") as f:
    unique_queries = list(set(line.strip() for line in f if line.strip()))

current_query = sys.argv[1]
close_matches = difflib.get_close_matches(current_query, unique_queries, n=1, cutoff=0.6)

if close_matches:
    print(close_matches[0])
else:
    print("No similar query found.")
