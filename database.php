$insert_sql = "INSERT INTO reading_progress (title, author, progress_percentage) VALUES (?, ?, ?)";
$stmt = $conn->prepare($insert_sql);
$stmt->bind_param("ssd", $book_title, $author, $progress_percentage); // s for string, d for double
if ($stmt->execute()) {
    echo "<p>Progress saved successfully!</p>";
} else {
    echo "<p>Error saving progress: " . $stmt->error . "</p>";
}
$stmt->close();