<html>
<head>
	<title>Pokedex</title>
	<!-- Import Bootstrap's classnames from CSS File -->
	<link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- Import Google's Material Icons CSS File -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0&icon_names=search" />
<head>

<body>

<div class="container">
<h1 style="color: #FFCC00;">Pokedex</h1>
<p class="lead">Collection of all 1025 Pokemon from every generation sourced from <a href="https://pokemondb.net/pokedex/national" style="color: #FFCC00;">PokemonDB</a></p>
<p>Try searching for Pokemon <strong>names</strong> (e.g. Bulbasaur), or <strong>descriptions</strong> (e.g. green bulb).</p>

<form action="search.php" method="post">
	<div class="input-group" style="width: 33%;">
		<input class="form-control" type="text" size=40 name="search_string" placeholder="Search the dex..." value="<?php echo $_POST["search_string"];?>"/>
		<div class="input-group-append">
			<button type="submit" class="btn btn-outline-secondary">
				<span class="material-symbols-outlined">search</span>
			</button>			
		</div>
	</div>
</form>

<?php
	if (isset($_POST["search_string"])) {
		$search_string = $_POST["search_string"];
		
		file_put_contents("logs.txt", $search_string.PHP_EOL, FILE_APPEND | LOCK_EX);

		# Top 1 related queries
		$related_query = trim(shell_exec("python3 related_queries.py \"$search_string\" 2>&1"));
		echo "<p>Similar Query: $related_query</p>";

		# Top 1 popular queries
		$popular_query = trim(shell_exec("python3 popular_queries.py"));
		echo "<p>Most Popular Query: $popular_query</p>";

		$qfile = fopen("query.py", "w");

		fwrite($qfile, "import pyterrier as pt\nif not pt.started():\n\tpt.init()\n\n");
		fwrite($qfile, "import pandas as pd\nqueries = pd.DataFrame([[\"q1\", \"$search_string\"]], columns=[\"qid\",\"query\"])\n");
		fwrite($qfile, "index = pt.IndexFactory.of(\"./pokedex_index_v4/\")\n"); #Make sure to change the index name here
		fwrite($qfile, "tf_idf = pt.BatchRetrieve(index, wmodel=\"Hiemstra_LM\")\n"); #Make sure to change the model here
		fwrite($qfile, "results = tf_idf.transform(queries)\n");

		for ($i=0; $i<5; $i++) {
			fwrite($qfile, "print(index.getMetaIndex().getItem(\"name\",results.docid[$i]))\n");
			fwrite($qfile, "print(index.getMetaIndex().getItem(\"description\",results.docid[$i]))\n");
			fwrite($qfile, "print(index.getMetaIndex().getItem(\"dexno\",results.docid[$i]))\n");
			fwrite($qfile, "print(index.getMetaIndex().getItem(\"type\",results.docid[$i]))\n");
			fwrite($qfile, "print(index.getMetaIndex().getItem(\"img\",results.docid[$i]))\n");
			fwrite($qfile, "print(index.getMetaIndex().getItem(\"url\",results.docid[$i]))\n");

   		}
   
   		fclose($qfile);

   		exec("ls | nc -u 127.0.0.1 10025"); #Make sure to change the port num here
   		sleep(3);

   		$stream = fopen("output", "r");

   		$line=fgets($stream);

   		while(($line=fgets($stream))!=false) {
			$name = trim($line); // First line is name
			$description = trim(fgets($stream)); // Next line is description
			$dexno = trim(fgets($stream)); // Dex number
    		$type = trim(fgets($stream)); // Pokémon type (if needed elsewhere)
			$img = trim(fgets($stream));
    		$url = trim(fgets($stream)); // Read URL		

			echo "<div class=\"card mb-4\">";
			echo "<div class=\"d-flex\">";
			echo "<div class=\"d-flex flex-column align-items-center m-4\">";
			echo "<h5 class=\"card-title\"><a style=\"color: #FFCC00;\" href=\"$url\">$dexno: $name</a></h5>";
			echo "<img src=\"$img\" width=\"100\" height=\"100\">\n";
			echo "<p>$type</p>";
			echo "</div>";
			echo "<div class=\"card-body\">";
			echo "<p>$description</p>\n";
			echo "</div>";
			echo "</div>";
			echo "</div>";
   		}

   		fclose($stream);
   
  		exec("rm query.py");
  		exec("rm output");
   		}
?>

	</div>

</body>
</html>
