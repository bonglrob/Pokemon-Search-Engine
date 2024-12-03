from collections import Counter


with open("logs.txt", "r") as f:
    past_queries = [line.strip() for line in f]

query_counts = Counter(past_queries)
popular_queries = query_counts.most_common(1)
popular_queries = popular_queries[0][0]

if popular_queries:
    print(popular_queries)
else:
    print("No popular queries.")