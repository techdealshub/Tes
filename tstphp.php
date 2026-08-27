<?php
echo 'welvcom';
?>

<form method="POST">

    <label>Badge</label>
    <input type="text" name="badge">

    <label>Title</label>
    <input type="text" name="title">

    <label>Description</label>
    <textarea name="description" rows="5"></textarea>

    <label>Published Date</label>
    <input type="text" name="published_date">

    <label>Updated Date</label>
    <input type="text" name="updated_date">

    <label>Author</label>
    <input type="text" name="author">

    <label>Overall Score</label>
    <input type="text" name="overall_score">

    <label>Hero Image</label>
    <input type="text" name="hero_image">

    <label>Phone Name</label>
    <input type="text" name="phone_name">

    <button type="submit" name="generate">
        Generate Review
    </button>

</form>