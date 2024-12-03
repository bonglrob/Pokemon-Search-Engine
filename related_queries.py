import sys
import difflib

# Load and filter unique queries from logs.txt
with open("pokemon_names.txt", "r", encoding="utf-8") as f:
    unique_queries = list(set(line.strip() for line in f if line.strip()))

# Get the current query from command-line arguments
current_query = sys.argv[1]

# Use difflib to find the closest matches
close_matches = difflib.get_close_matches(current_query, unique_queries, n=1, cutoff=0.6)

# Output the result
if close_matches:
    print(close_matches[0])
else:
    print("No similar query found.")
