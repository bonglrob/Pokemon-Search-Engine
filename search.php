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
<h1 style="color: #CAA81D;">Pokedex</h1>
<p>Collection of all 1000 Pokemon from every generation.</p>
<p>Try searching for Bulbasaur (Pokemon names) or Grass (Pokemon types).</p>
<form action="search.php" method="post">
	<input type="text" size=40 name="search_string" value="<?php echo $_POST["search_string"];?>"/>
	<input type="submit" value="Search"/>
</form>

<?php { ?>
	<div class="card">
		<div class="card-body">
			<h5 class="card-title">Recommended</h5>
			<img src="[insert pokemon img]">
			<div>
				<p>[Insert Pokemon name]</p>
				<p>Types: [Insert Pokemon type]</p>
			</div>
<?php }
?>

<?php
	if (isset($_POST["search_string"])) {
		$search_string = $_POST["search_string"];
		
		file_put_contents("logs.txt", $search_string.PHP_EOL, FILE_APPEND | LOCK_EX);

		$qfile = fopen("query.py", "w");

		fwrite($qfile, "import pyterrier as pt\nif not pt.started():\n\tpt.init()\n\n");
		fwrite($qfile, "import pandas as pd\nqueries = pd.DataFrame([[\"q1\", \"$search_string\"]], columns=[\"qid\",\"query\"])\n");
		fwrite($qfile, "index = pt.IndexFactory.of(\"./poke_data_index/\")\n"); #Make sure to change the index name here
		fwrite($qfile, "tf_idf = pt.BatchRetrieve(index, wmodel=\"TF_IDF\")\n"); #Make sure to change the model here
		fwrite($qfile, "results = tf_idf.transform(queries)\n");

		for ($i=0; $i<5; $i++) {
			fwrite($qfile, "print(index.getMetaIndex().getItem(\"filename\",results.docid[$i]))\n");
			fwrite($qfile, "if index.getMetaIndex().getItem(\"title\", results.docid[$i]).strip() != \"\":\n");
			fwrite($qfile, "\tprint(index.getMetaIndex().getItem(\"title\",results.docid[$i]))\n");
			fwrite($qfile, "else:\n\tprint(index.getMetaIndex().getItem(\"filename\",results.docid[$i]))\n");
   		}
   
   		fclose($qfile);

   		exec("ls | nc -u 127.0.0.1 10037"); #Make sure to change the port num here
   		sleep(3);

   		$stream = fopen("output", "r");

   		$line=fgets($stream);

   		while(($line=fgets($stream))!=false) {
   			$clean_line = preg_replace('/\s+/',',',$line);
   			$record = explode("./", $clean_line);
   			$line = fgets($stream);
   			echo "<a href=\"http://$record[1]\">".$line."</a><br/>\n";
   		}

   		fclose($stream);
   
  		exec("rm query.py");
  		exec("rm output");
   		}
?>

	</div>

</body>
</html>
