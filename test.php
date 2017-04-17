<?php 

	if($_SERVER['REQUEST_METHOD'] == 'POST'){
		$labels = $_POST['tags'];
		foreach($labels as $label){
			echo $label . '<br>';
		}
	}
?>

<!DOCTYPE html>
<html>
<head>
	<title>test</title>
</head>
<body>
<form action="test.php" method="post">
	                <p>Select between 1 to 5 tags for your post. An accurate title and use of relevant
                tags helps others explore your post.</p>

                <input type="checkbox" name="tags[]" value="funny" id="funny">
                <label for="funny" class="tag-label">Funny</label>

                <input type="checkbox" name="tags[]" value="art" id="art">
                <label for="art" class="tag-label">Art</label>

                <input type="checkbox" name="tags[]" value="nature" id="nature">
                <label for="nature" class="tag-label">nature</label>

                <input type="checkbox" name="tags[]" value="politics" id="politics">
                <label for="politics" class="tag-label">Politics</label>

                <input type="checkbox" name="tags[]" value="sports" id="sports">
                <label for="sports" class="tag-label">Sports</label>

                <input type="checkbox" name="tags[]" value="hobbies" id="hobbies">
                <label for="hobbies" class="tag-label">Hobbies</label>

                <input type="checkbox" name="tags[]" value="work" id="work">
                <label for="work" class="tag-label">Work</label>

                <input type="checkbox" name="tags[]" value="education" id="education">
                <label for="education" class="tag-label">Education</label>

                <input type="checkbox" name="tags[]" value="events" id="events">
                <label for="events" class="tag-label">Events</label>

                <input type="checkbox" name="tags[]" value="travel" id="travel">
                <label for="travel" class="tag-label">Travel</label>

                <input type="checkbox" name="tags[]" value="gaming" id="gaming">
                <label for="gaming" class="tag-label">Gaming</label>

                <input type="checkbox" name="tags[]" value="other" id="other">
                <label for="other" class="tag-label">Other</label>

                
                <input type="submit" value="Publish">
</form>
</body>
</html>