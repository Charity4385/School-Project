<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reading Progress Chart</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }

        header {
            background-color: #333;
            color: white;
            padding: 1rem 0;
            text-align: center;
        }

        nav ul {
            list-style: none;
            padding: 0;
            margin: 1rem 0;
            text-align: center;
        }

        nav ul li {
            display: inline;
            margin: 0 1rem;
        }

        nav a {
            color: white;
            text-decoration: none;
        }

        section {
            padding: 2rem;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        main {
            background-color: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 800px;
        }

        h1 {
            text-align: center;
            margin-bottom: 1.5rem;
        }

        #bookProgressChart {
            width: 800px !important; 
            height: 600px !important; 
            margin: 0 auto;
        }

        footer {
            background-color: #333;
            color: white;
            text-align: center;
            padding: 1rem 0;
            position: relative;
            bottom: 0;
            width: 100%;
        }
    </style>
</head>
<body>
    <header>
        <h1>Welcome to book lovers</h1>
        <nav>
            <ul>
                <li><a href="index.html">Home</a></li>
                <li><a href="book_details.php">Book-Details</a></li>
                <li><a href="progress_tracking.php">Progress-Tracking</a></li>
                <li><a href="reading_progress.php">Reading-history Chart</a></li>
                <li><a href="reading_goals.php">Reading Goals</a></li>
            </ul>
        </nav>
    </header>
    <section>
        <div>
            <main>
                <h1>Reading History Chart</h1>

                <?php
                // Database connection details
                $servername = "localhost";
                $username = "root";
                $password = "";
                $dbname = "Progress_tracking";

                // Create connection
                $conn = new mysqli($servername, $username, $password, $dbname);

                // Check connection
                if ($conn->connect_error) {
                    die("Connection failed: " . $conn->connect_error);
                }

                // Fetch data for the chart
                $chart_sql = "SELECT title, author, date, progress FROM readings";
                $chart_result = $conn->query($chart_sql);
                $chart_data = [];

                if ($chart_result->num_rows > 0) {
                    while ($row = $chart_result->fetch_assoc()) {
                        $chart_data[] = $row;
                    }
                }

                echo "<script>var chartData = " . json_encode($chart_data) . ";</script>"; // Pass data to JavaScript

                $conn->close();
                ?>

                <canvas id="bookProgressChart"></canvas>
            </main>
        </div>
    </section>
    <footer>
        <p>&copy; 2025 Book Lovers. All rights reserved.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const ctx = document.getElementById('bookProgressChart').getContext('2d');
        const labels = chartData.map(book => `${book.title} (${book.author}) (${book.date})`);
        const data = chartData.map(book => book.progress);

        new Chart(ctx, {
            type: 'pie',
            data: {
                labels: labels,
                datasets: [{
                    data: data,
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.7)',
                        'rgba(54, 162, 235, 0.7)',
                        'rgba(255, 206, 86, 0.7)',
                        'rgba(75, 192, 192, 0.7)',
                    ],
                    borderWidth: 1,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,  
                        position: 'right',
                        labels: {
                            font: {
                                size: 14 
                            }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.label || '';
                                if (context.parsed !== null) {
                                    label += ': ' + context.parsed + '%';
                                }
                                return label;
                            }
                        },
                        bodyFont: {
                            size: 12 
                        },
                        titleFont: {
                            size: 16 
                        }
                    }
                },
            },
        });
    </script>
</body>
</html>