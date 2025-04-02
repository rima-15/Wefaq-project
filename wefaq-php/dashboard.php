<?php
session_start();
$user_id = $_SESSION['user_id'];
include 'connection.php';

$query_tasks = "
    SELECT 
        task.task_deadline AS task_deadline, 
        task.task_name, 
        'task' AS type
    FROM task 
    WHERE task.assigned_to = '$user_id'
";

$query_projects = "
    SELECT 
        project.project_deadline AS task_deadline, 
        project.project_name, 
        'project' AS type
    FROM project
    WHERE project.project_id IN (
        SELECT project_id 
        FROM projectteam 
        WHERE projectteam.user_id = '$user_id'
    )
";

$query = "($query_tasks) UNION ($query_projects)";

$result2 = mysqli_query($conn, $query);
$events = [];

if (mysqli_num_rows($result2) > 0) {
    while ($row = mysqli_fetch_assoc($result2)) {
        $events[] = $row;
    }
}


echo '<script>';
echo 'var events = ' . json_encode($events) . ';';
echo '</script>';

$query_total_projects = "
    SELECT COUNT(DISTINCT project_id) AS total_projects
    FROM projectteam 
    WHERE user_id = '$user_id'
";

$query_in_progress_projects = "
    SELECT COUNT(DISTINCT projectteam.project_id) AS in_progress_projects
    FROM projectteam
    JOIN project ON projectteam.project_id = project.project_id
    WHERE projectteam.user_id = '$user_id' AND project.status = 'in progress'
";

$query_lead_projects = "
    SELECT COUNT(DISTINCT project_id) AS lead_projects
    FROM project
    WHERE leader_ID = '$user_id' 
";

$result_total_projects = mysqli_query($conn, $query_total_projects);
$result_in_progress_projects = mysqli_query($conn, $query_in_progress_projects);
$result_lead_projects = mysqli_query($conn, $query_lead_projects);

$total_projects = mysqli_fetch_assoc($result_total_projects)['total_projects'];
$in_progress_projects = mysqli_fetch_assoc($result_in_progress_projects)['in_progress_projects'];
$lead_projects = mysqli_fetch_assoc($result_lead_projects)['lead_projects'];

echo '<script>';
echo "var totalProjects = $total_projects;";
echo "var inProgressProjects = $in_progress_projects;";
echo "var leadProjects = $lead_projects;";
echo '</script>';

$query_active_projects = "
    SELECT project.project_id, project.project_name
    FROM project
    JOIN projectteam ON project.project_id = projectteam.project_id
    WHERE projectteam.user_id = '$user_id' AND project.status = 'in progress'
";
$result_active_projects = mysqli_query($conn, $query_active_projects);
$active_projects = [];
while ($row = mysqli_fetch_assoc($result_active_projects)) {
    $active_projects[] = $row;
}

$query_leader_projects = "
    SELECT project_id, project_name
    FROM project
    WHERE leader_ID = '$user_id'
";
$result_leader_projects = mysqli_query($conn, $query_leader_projects);
$leader_projects = [];
while ($row = mysqli_fetch_assoc($result_leader_projects)) {
    $leader_projects[] = $row;
}

echo '<script>';
echo 'var activeProjects = ' . json_encode($active_projects) . ';';
echo 'var leaderProjects = ' . json_encode($leader_projects) . ';';
echo '</script>';

$query_completed_tasks = "
    SELECT COUNT(*) AS completed_tasks
    FROM task
    WHERE assigned_to = '$user_id' AND status = 'Completed'
";
$result_completed = mysqli_query($conn, $query_completed_tasks);
$completed_tasks = mysqli_fetch_assoc($result_completed)['completed_tasks'];

$query_total_tasks = "
    SELECT COUNT(*) AS total_tasks
    FROM task
    WHERE assigned_to = '$user_id'
";
$result_total = mysqli_query($conn, $query_total_tasks);
$total_tasks = mysqli_fetch_assoc($result_total)['total_tasks'];

echo "<script>";
echo "var completedTasks = $completed_tasks;";
echo "var totalTasks = $total_tasks;";
echo "var remainingTasks = totalTasks - completedTasks;";
echo "</script>";

$query = "
    SELECT s.skill_name, AVG(r.rating_value) AS avg_rating
    FROM rate r
    JOIN skill s ON r.skill_ID = s.skill_ID
    WHERE r.rated_ID = $user_id 
    GROUP BY r.skill_ID
    ORDER BY s.skill_ID
";

$result = mysqli_query($conn, $query);
$skill_names = [];
$rating_values = [];

while ($row = mysqli_fetch_assoc($result)) {
    $skill_names[] = $row['skill_name'];
    $rating_values[] = round($row['avg_rating'], 1);
}


$average_rating = count($rating_values) > 0 ? round(array_sum($rating_values) / count($rating_values), 1) : 0;

echo "<script>";
echo "const ratingLabels = " . json_encode($skill_names) . ";";
echo "const ratingData = " . json_encode($rating_values) . ";";
echo "const averageRating = $average_rating;";
echo "</script>";

$query = "SELECT task.*, project.project_name AS project_name FROM task 
          JOIN project ON task.project_id = project.project_id
          WHERE task.assigned_to = '$user_id' AND task.status IN ('Not Started', 'In Progress')";
$result = mysqli_query($conn, $query);
?> 
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
        <title>Project Hub - Home Page</title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">   
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

        <style>
            * {
                box-sizing: content-box !important;
            }
            body {
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                margin: 0;
                padding: 0;
                color: white;
                background: white;
                overflow-x: hidden;
                text-align: center;

            }

            .index-nav {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 15px 30px;
                position: fixed;
                width: 100%;
                top: 0;
                z-index: 1000;
                background: rgba(255, 255, 255, 0.05);
                backdrop-filter: blur(10px);
                box-sizing: border-box;
            }

            .logo img {
                width: 120px;
            }

            .summary-section {
                background: linear-gradient(135deg,#f8f9fa, #9096DE);
                z-index: 1;
            }

            .charts-wrapper {
                display: flex;
                gap: 30px;
                justify-content: center;
                align-items: center;
                flex-wrap: wrap;
                margin-top: 50px;
            }
            .chart-card {
                background: white;
                color: #6064D5;
                padding: 10px;
                border-radius: 20px;
                box-shadow: 0 4px 15px rgba(0,0,0,0.1);
                display: flex;
                flex-direction: column;
                align-items: center;
                width: 160px;
                height: 250px;
            }

            .task-card,
            .project-card {
                width: 160px;
                height: 190px;
            }

            .rating-card,
            .chart-card:nth-child(2) {
                width: 250px;
                height: 250px;
            }

            .chart-card:hover {
                transform: translateY(-5px);
                box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
            }

            .chart-card canvas {
                max-width: 120px;
                max-height: 120px;
                height: auto;
                margin-top: 10px;
            }
            #ratingCircle{
                max-width: 140px;
                max-height: 140px;
                height: auto;
                margin-top: 10px;
            }
            .chart-card h3 {
                font-size: 1rem;
                margin-bottom: 0;
            }


            .project-number-box {
                font-weight: bold;
                font-size: 1.25rem;
                color: #6064D5;
            }

            .legend {
                display: grid;
                grid-template-columns: repeat(3, auto);
                font-size: 0.6rem;
                gap: 3px 8px;
                justify-content: center;
                margin-top: 8px;
            }

            .dot {
                width: 10px;
                height: 10px;
                border-radius: 50%;
                display: inline-block;
                margin-right: 5px;
            }

            .wave-divider {
                display: block;
                width: 100%;
                height: 43.5%;
            }

            footer {
                background: white;
                color: #9096DE;
                padding: 30px 0;
                text-align: center;
                box-shadow: 0 -5px 10px rgba(0, 0, 0, 0.1);
            }

            .footer-container {
                display: flex;
                justify-content: space-between;
                align-items: center;
                max-width: 1200px;
                margin: 0 auto;
                padding: 0 20px;
                flex-wrap: wrap;
            }

            .footer-logo img {
                width: 91px;
                transition: transform 0.3s ease-in-out;
            }

            .footer-logo img:hover {
                transform: scale(1.1);
            }

            .footer-contact p {
                margin: 5px 0;
                font-size: 17px;
            }

            .footer-social {
                display: flex;
                gap: 15px;
            }

            .social-icon {
                width: 50px;
                height: 50px;
                display: flex;
                align-items: center;
                justify-content: center;
                border-radius: 50%;
                background: white;
                color: #6064D5;
                font-size: 22px;
                text-decoration: none;
                transition: 0.3s ease-in-out;
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.2);
            }

            .social-icon:hover {
                background: #eed442;
                color: #333;
                transform: translateY(-5px);
            }
            .project-icon {
                justify-content: center;
                font-size: 3.2rem;
                color: #eed442;
                margin-bottom: 10px;
                margin-top: 20px;
            }



            .calendar-projects-section {
                display: flex;
                gap: 4rem;
                flex-wrap: wrap;
                margin-bottom: 3rem;
                background-color: white;
                justify-content: center;
                align-items: stretch;
            }
            .calendar, .active-projects {
                flex: 1;

            }
            .calendar {
                flex: 1 1 300px;
                min-width: 280px;
                max-width: 380px;
                padding: 1rem;
                background-color: #f9f9f9 !important;
                border-radius: 12px;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
                overflow-x: auto;
            }



            .calendar-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 1rem;
            }
            .calendar-header button {
                background: none;
                border: none;
                font-size: 18px;
                cursor: pointer;
            }
            .calendar-grid {
                display: grid;
                grid-template-columns: repeat(7, 1fr);
                gap: 8px;
                justify-items: center;
            }

            .calendar-day, .weekday {
                text-align: center;
                padding: 0.5rem;
            }
            .weekday {
                font-weight: bold;
                text-align: center;
                margin-bottom: 5px;
            }
            .calendar-day {
                width: 32px !important;
                height: 32px !important;
                font-size: 0.8rem;
                display: flex;
                align-items: center;
                justify-content: center;
                border-radius: 50%;
                border: 1px solid #ddd;
                background-color: #D5D9FB;
                position: relative;
                outline: none; /* Prevents black outline */

            }

            .calendar-day[data-task]:hover::after {
                content: attr(data-task);
                position: absolute;
                bottom: 50px;
                left: 50%;
                transform: translateX(-50%);
                background: rgba(0, 0, 0, 0.8);
                color: white;
                padding: 5px 10px;
                border-radius: 5px;
                white-space: nowrap;
                font-size: 12px;
            }


            .task-deadline ,.project-deadline {
                background-color: #eed442 !important;
                color: #fff;
            }
            .calendar-grid:last-child {
                margin-bottom: 0;
            }
            .event {
                display: flex;
                align-items: center;
                gap: 10px;
                margin: 10px;
            }
            .event-circle {
                width: 40px;
                height: 40px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                font-weight: bold;
                background-color: #eed442;
                color: #fff;
            }


            .my-tasks {
                background-color: #f9f9f9;
                text-align: center;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
                border-radius: 12px;
            }



            .active-projects {
                min-width: 250px;
                flex: 0 0 30%;
                align-self: flex-start;
                padding: 1rem;
                background-color: #f9f9f9;
                transition: max-height 0.3s ease;
                overflow: hidden;
                border-radius: 12px;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            }


            .active-projects h2 {
                margin-bottom: 0.5rem;

            }
            ul.project-links {
                list-style: none;
                padding-left: 0;
            }
            .project-links {
                margin-top: 10px;
                padding-left: 2.5rem;
                margin-left:2.5rem;
                max-height: 6.5rem;
                overflow: hidden;
                transition: max-height 0.3s ease;
            }

            .project-links.expanded {
                max-height: none;
            }

            .project-links li a {
                display: flex;
                align-items: center;
                gap: 8px;
                text-decoration: none;
                font-family: 'Segoe UI', sans-serif;
                font-size: 0.95rem;
                transition: color 0.3s ease;
            }



            .project-links li a:hover {
                color: #9096DE;
            }




            .toggle-button:hover {
                color: #C2C6F0;
                border-color: #C2C6F0;
            }
            .toggle-button {
                display: flex;
                justify-content: center;
                align-items: center;
                margin: 1rem auto 0;
                align-items: center;
                gap: 6px;
                background: transparent;
                color: gray;
                font-size: 1rem;
                border: none;
                cursor: pointer;
                padding: 0.5rem;
                transition: all  0.3s ease;
            }

            .toggle-button i {
                transition: transform 0.3s ease;
            }

            .toggle-button.rotate i {
                transform: rotate(180deg);
            }



            .my-tasks h2 {
                padding-top: 10px;

                margin-bottom: 1rem;
            }

            .cards-wrapper {

                overflow-x: auto;
                white-space: nowrap;
                cursor: grab;
                max-width: 80%;
                margin: 0 auto;
            }

            .cards-container {

                display: flex;
                gap: 1rem;
                justify-content: flex-start;
            }

            .task-card2 {
                min-width: 250px;
                max-width: 280px;
                flex-shrink: 0;
                background: #fdfbff;
                border-radius: 8px;
                overflow: hidden;
                box-shadow: 0 2px 6px rgba(0,0,0,0.05);
                margin-bottom: 15px;

            }

            .task-header {
                padding: 0.75rem;
                color: white;
                font-weight: bold;
                text-align: center;
                font-size: 14px;
                background-color: #C2C6F0;
            }

            .task-body {
                padding: 1.25rem;
            }

            .task-body h3 {
                margin-top: 0;
                color: #4a3c74;
            }
            .task-info-row {
                display: flex;
                align-items: flex-start;
                gap: 10px;
                margin-bottom: 12px;
                font-size: 0.9rem;
                color: #333;
                line-height: 1.4;
                padding: 8px;
            }

            .task-info-row i {
                width: 20px;
                font-size: 1rem;
                color: #606060;
                margin-top: 2px;
            }

            .task-massage{
                text-align: center;
            }

            .info-label {
                width: 90px;
                font-weight: 500;
                color: #555;
            }

            .info-value {
                flex-grow: 1;
                font-weight: 600;
                color: #222;
                word-wrap: break-word;
            }

            .info-value a {
                flex-grow: 1;
                font-weight: bold;
                color:#555;
            }

            .info-value a:hover {
                flex-grow: 1;
                font-weight: bold;
                color:#9096DE;
            }


            .status {
                padding: 2px 8px;
                border-radius: 6px;
                font-size: 0.8rem;
                color: white;
            }

            .status.working {
                background-color: rgba(238, 212, 66, 0.1);
                color: #EED442;
            }
            .status.pending {
                background-color: #f5f5f5;
                color: #666;
            }


            .today-marker {
                position: relative;
                background-color:#D3D3D3;
            }

            .today-marker:hover::after {
                content: "Today";
                position: absolute;
                bottom: 115%;
                left: 50%;
                transform: translateX(-50%);
                background: rgba(0, 0, 0, 0.8);
                color: white;
                padding: 4px 8px;
                border-radius: 6px;
                font-size: 12px;
                white-space: nowrap;
                z-index: 10;
            }
            .project-stats-row {
                display: flex;
                gap: 1rem;
                margin-top: 1rem;
                margin-bottom: 1rem;
                justify-content: center;
            }
            .stat-label {
                font-size: 0.8rem;
                font-weight: bold;
                opacity: 0.85;
                color: gray;
                text-align: left;
            }

            .tiny {
                font-size: 0.8rem;
                font-weight: bold;
                opacity: 0.85;
                color: gray;
                text-align: right;
                margin-top: 0;
                transform: translateY(-3px);
            }

            .numerator,
            .denominator {
                font-size: 1.2rem;
                font-weight: bold;
            }

            .slash {
                font-weight: bold;
                font-size: 1.2rem;
                margin: 0 4px;
                vertical-align: middle;
                color: #eed442;
            }

            .stat-card-clean {
                background-color: #C2C6F0;
                border-radius: 12px;
                padding: 0.5rem 0.75rem;
                width: 100px; 
                text-align: center;
                color: white;
                box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
                display: flex;
                flex-direction: column;
                justify-content: center;
                align-items: center;
                transition: transform 0.3s ease, box-shadow 0.3s ease;
            }

            .stat-card-clean:hover {
                transform: scale(1.05);
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            }

            .stat-card-clean canvas {
                max-width: 70px;
                max-height: 70px;
                margin-bottom: 4px;
                margin-top: 5px;
            }

            .wh{

                color:black;
                padding-left: 50px;
                padding-right: 50px;
            }

            #MainTitle{
                padding-top: 50px;
                color:white;
            }
            .wh {
                background: white ;
            }
            main{
                margin:0 ;
                padding:0;
                background:linear-gradient(135deg, #f8f9fa, #636bc1);
            }
            .main-content{
                margin:0 !important;
                padding:0 !important;
            }
            header{
                margin-top: 10px;
                margin: 1rem 2rem 0rem 2rem;
            }
            .dashboard-header{
                padding: 1rem 2rem !important;
            }
        </style>
        <link rel="stylesheet" href="styles.css">
    </head>
    <body> 
        <div class="home-container dashboard-container">
            <main class="main-content">
            <div class="summary-section ">
                <h1 id="MainTitle">Performance Summary</h1>
                <div class="charts-wrapper">
                    <div class="chart-card project-card">
                        <h3>Total Projects</h3>
                        <i class="fas fa-folder-open project-icon" ></i>
                        <p  id="total-projects-number" class="project-number-box">12</p>
                    </div>

                    <div class="chart-card">
                        <h3>Overall Rating</h3>
                        <canvas id="ratingCircle" ></canvas>
                        <div class="legend">

                        </div>
                    </div>

                    <div class="chart-card task-card">
                        <h3>Tasks</h3>
                        <canvas id="tasksChart" width="140" height="140"></canvas>
                        <div class="legend">
                            <span><span class="dot" style="background:#eed442;"></span>Completed</span>
                            <span><span class="dot" style="background:#d3d3d3;"></span>Remaining</span>
                        </div>
                    </div>


                </div>
                     
                <svg class="wave-divider" viewBox="0 0 1440 200" xmlns="http://www.w3.org/2000/svg">
                <path fill="white" fill-opacity="1" d="M0,256L80,234.7C120,213,240,171,360,165.3C480,160,600,192,720,186.7C840,181,960,139,1080,128C1200,117,1320,149,1380,170.7L1440,192L1440,320L0,320Z"></path>
                </svg>
            </div>
            <div class="wh">           <!-- 2. Calendar 3. Active Projects -->
                <div class="calendar-projects-section">
                    <!-- Active Projects -->
                    <section class="active-projects">
                        <h2>Active Projects</h2>
                        <div class="project-stats-row">
                            <div class="stat-card-clean" id="activeCard">
                                <div class="stat-label"> Your Active Projects </div>
                                <canvas id="inProgressChart" ></canvas>


                            </div>

                            <div class="stat-card-clean" id="leaderCard">
                                <div class="stat-label">Projects You Lead</div>
                                <canvas id="leadProjectsChart" ></canvas>


                            </div>
                        </div>


                        <hr>
                        <ul class="project-links" id="projectList">
                            <li class="task-info-row">
                                <i class="fas fa-diagram-project"></i>

                                <span class="info-value"><a href="project.html">Wefaq</a></span>
                            </li>
                            <li class="task-info-row">
                                <i class="fas fa-diagram-project"></i>

                                <span class="info-value"><a href="project.html">Network</a></span>
                            </li>
                            <li class="task-info-row">
                                <i class="fas fa-diagram-project"></i>

                                <span class="info-value"><a href="project.html">Data Science</a></span>
                            </li>

                            <li class="task-info-row">
                                <i class="fas fa-diagram-project"></i>

                                <span class="info-value"><a href="project.html">Data Science</a></span>
                            </li>
                            <li class="task-info-row">
                                <i class="fas fa-diagram-project"></i>

                                <span class="info-value"><a href="project.html">Data Science</a></span>
                            </li>
                            <li class="task-info-row">
                                <i class="fas fa-diagram-project"></i>

                                <span class="info-value"><a href="project.html">Data Science</a></span>
                            </li>
                        </ul>
                        <button class="toggle-button">
                            <span class="btn-text">Show More</span>
                            <i class="fas fa-chevron-down"></i>
                        </button>
                    </section>
                    <!-- Calendar -->
                    <aside class="calendar">
                        <div class="calendar-header">
                            <button onclick="prevMonth()">&#9665;</button>
                            <span id="calendar-header"></span>
                            <button onclick="nextMonth()">&#9655;</button>
                        </div>
                        <div class="calendar-grid" id="weekday-labels"></div>
                        <div class="calendar-grid" id="calendar-grid"></div>
                    </aside>


                </div>

                <!-- 4. My Tasks -->
                <section class="my-tasks">
                    <h2>My Tasks</h2>
                    <div class="cards-wrapper" id="taskScroll">
                        <div class="cards-container">
                            <?php
                            if (mysqli_num_rows($result) > 0) {

                                while ($task = mysqli_fetch_assoc($result)) {

                                    if ($task['status'] == 'in progress') {
                                        $statusClass = 'working';
                                    } elseif ($task['status'] == 'not started') {
                                        $statusClass = 'pending';
                                    } else {
                                        $statusClass = '';
                                    }

                                    echo '<div class="task-card2">';
                                    echo '<div class="task-header">' . $task['task_name'] . '</div>';
                                    echo '<div class="task-body">';

                                    echo '<div class="task-info-row">';
                                    echo '<i class="fas fa-diagram-project"></i>';
                                    echo '<span class="info-label">Project:</span>';
                                    echo '<span class="info-value"><a href="project.html">' . $task['project_name'] . '</a></span>';
                                    echo '</div>';

                                    echo '<div class="task-info-row">';
                                    echo '<i class="fas fa-calendar-alt"></i>';
                                    echo '<span class="info-label">Deadline:</span>';
                                    echo '<span class="info-value">' . $task['task_deadline'] . '</span>';
                                    echo '</div>';

                                    echo '<div class="task-info-row">';
                                    echo '<i class="fas fa-tasks"></i>';
                                    echo '<span class="info-label">Status:</span>';
                                    echo '<span class="info-value status ' . $statusClass . '">' . $task['status'] . '</span>';
                                    echo '</div>';
                                    echo '</div>';
                                    echo '</div>';
                                }
                            } else {

                                echo '<div class="task-card2">';
                                echo '<div class="task-header">No Tasks Assigned</div>';
                                echo '</div>';
                            }
                            ?>
                        </div>
                    </div>
                </section>
                <br><br><br>
            </div>
                
        </main>
            <footer>
                <div class="footer-container">
                    <div class="footer-logo">
                        <img src="logoNOback.png" alt="Logo">
                    </div>
                    <div class="footer-contact">
                        <p><strong>Contacts:</strong></p>
                        <p>0558944669</p>
                        <p>wefaq@email.com</p>
                    </div>
                    <div class="footer-social">
                        <a href="#" class="social-icon"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="social-icon"><i class="fab fa-whatsapp"></i></a>
                        <a href="#" class="social-icon"><i class="fab fa-x-twitter"></i></a>
                    </div>
                </div>
            </footer>
        </div>
        <script src="js/components.js"></script>
<script>
    // تسجيل بلجن رسم النص داخل الدوائر
    Chart.register({
        id: 'centerText',
        beforeDraw: function (chart) {
            if (chart.config.options.elements && chart.config.options.elements.center) {
                const ctx = chart.ctx;
                const centerConfig = chart.config.options.elements.center;
                const txt = centerConfig.text;
                const color = centerConfig.color || '#6064D5';
                ctx.font = 'bold 20px Poppins';
                ctx.fillStyle = color;
                ctx.textAlign = 'center';
                ctx.textBaseline = 'middle';
                const centerX = (chart.chartArea.left + chart.chartArea.right) / 2;
                const centerY = (chart.chartArea.top + chart.chartArea.bottom) / 2;
                ctx.fillText(txt, centerX, centerY);
            }
        }
    });

    document.addEventListener("DOMContentLoaded", function () {
        // 1. عرض رقم المشاريع
        const totalElement = document.getElementById('total-projects-number');
        if (totalElement && typeof totalProjects !== 'undefined') {
            totalElement.textContent = totalProjects;
        }

        // 2. تشارت المشاريع الجارية
        new Chart(document.getElementById('inProgressChart'), {
            type: 'doughnut',
            data: {
                labels: ['In Progress', 'Other'],
                datasets: [{
                    data: [inProgressProjects, totalProjects - inProgressProjects],
                    backgroundColor: ['#eed442', '#D3D3D3'],
                    borderWidth: 0
                }]
            },
            options: {
                cutout: '70%',
                plugins: { legend: { display: false } },
                elements: {
                    center: {
                        text: `${inProgressProjects}/${totalProjects}`
                    }
                }
            }
        });

        // 3. تشارت المشاريع التي تقودها
        new Chart(document.getElementById('leadProjectsChart'), {
            type: 'doughnut',
            data: {
                labels: ['Lead', 'Other'],
                datasets: [{
                    data: [leadProjects, totalProjects - leadProjects],
                    backgroundColor: ['#eed442', '#D3D3D3'],
                    borderWidth: 0
                }]
            },
            options: {
                cutout: '70%',
                plugins: { legend: { display: false } },
                elements: {
                    center: {
                        text: `${leadProjects}/${totalProjects}`
                    }
                }
            }
        });

        // 4. تشارت المهام
        const ctx = document.getElementById('tasksChart').getContext('2d');
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Completed', 'Remaining'],
                datasets: [{
                    data: [completedTasks, remainingTasks],
                    backgroundColor: ['#eed442', '#d3d3d3'],
                    borderWidth: 0
                }]
            },
            options: {
                cutout: '70%',
                plugins: { legend: { display: false } },
                elements: {
                    center: {
                        text: `${completedTasks}/${totalTasks}`
                    }
                }
            }
        });

        // 5. تشارت التقييم
        const ratingColors = ['#f87171', '#facc15', '#a5b4fc', '#6ee7b7', '#fbbf24', '#5eead4'];

        const legendContainer = document.querySelector('.legend');
        ratingLabels.forEach((label, index) => {
            const legendItem = document.createElement('span');
            legendItem.innerHTML = `<span class="dot" style="background:${ratingColors[index]}"></span>${label}`;
            legendContainer.appendChild(legendItem);
        });

        new Chart(document.getElementById('ratingCircle'), {
            type: 'doughnut',
            data: {
                labels: ratingLabels,
                datasets: [{
                    data: ratingData,
                    backgroundColor: ratingColors,
                    borderWidth: 0
                }]
            },
            options: {
                cutout: '70%',
                plugins: { legend: { display: false } },
                elements: {
                    center: {
                        text: averageRating
                    }
                }
            }
        });

        // 6. التقويم
        let currentMonth = new Date().getMonth();
        let currentYear = new Date().getFullYear();

        function generateCalendar(month, year) {
            const calendarGrid = document.getElementById('calendar-grid');
            const calendarHeader = document.getElementById('calendar-header');
            const weekdayLabels = document.getElementById('weekday-labels');
            const weekdays = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

            calendarHeader.textContent = `${new Date(year, month).toLocaleString('default', { month: 'long' })} ${year}`;
            weekdayLabels.innerHTML = '';
            weekdays.forEach(day => {
                const dayLabel = document.createElement('div');
                dayLabel.classList.add('weekday');
                dayLabel.textContent = day;
                weekdayLabels.appendChild(dayLabel);
            });

            calendarGrid.innerHTML = '';
            const firstDay = new Date(year, month, 1).getDay();
            const daysInMonth = new Date(year, month + 1, 0).getDate();

            for (let i = 0; i < firstDay; i++) {
                const emptyCell = document.createElement('div');
                emptyCell.classList.add('calendar-day');
                calendarGrid.appendChild(emptyCell);
            }

            for (let day = 1; day <= daysInMonth; day++) {
                const dayCell = document.createElement('div');
                dayCell.classList.add('calendar-day');
                dayCell.textContent = day;

                const today = new Date();
                if (day === today.getDate() && month === today.getMonth() && year === today.getFullYear()) {
                    dayCell.classList.add('today-marker');
                    dayCell.setAttribute('data-task', 'Today');
                }

                events.forEach(event => {
                    const eventDate = new Date(event.task_deadline);
                    if (eventDate.getDate() === day && eventDate.getMonth() === month && eventDate.getFullYear() === year) {
                        dayCell.classList.add('highlight');
                        dayCell.setAttribute('data-task', event.task_name);
                        dayCell.style.backgroundColor = "#eed442";
                    }
                });

                calendarGrid.appendChild(dayCell);
            }
        }

        window.prevMonth = function () {
            currentMonth--;
            if (currentMonth < 0) {
                currentMonth = 11;
                currentYear--;
            }
            generateCalendar(currentMonth, currentYear);
        };

        window.nextMonth = function () {
            currentMonth++;
            if (currentMonth > 11) {
                currentMonth = 0;
                currentYear++;
            }
            generateCalendar(currentMonth, currentYear);
        };

        generateCalendar(currentMonth, currentYear);

        // 7. Show More
        const projectsList = document.getElementById('projectList');
        const toggleBtn = document.querySelector('.toggle-button');

        toggleBtn.addEventListener('click', () => {
            projectsList.classList.toggle('expanded');
            const isExpanded = projectsList.classList.contains('expanded');
            toggleBtn.querySelector('.btn-text').textContent = isExpanded ? 'Show Less' : 'Show More';
            toggleBtn.classList.toggle('rotate', isExpanded);
        });

        // 8. Drag scrolling for tasks
        const taskScroll = document.getElementById('taskScroll');
        let isDown = false, startX, scrollLeft;

        taskScroll.addEventListener('mousedown', (e) => {
            isDown = true;
            taskScroll.classList.add('active');
            startX = e.pageX - taskScroll.offsetLeft;
            scrollLeft = taskScroll.scrollLeft;
        });

        taskScroll.addEventListener('mouseleave', () => {
            isDown = false;
            taskScroll.classList.remove('active');
        });

        taskScroll.addEventListener('mouseup', () => {
            isDown = false;
            taskScroll.classList.remove('active');
        });

        taskScroll.addEventListener('mousemove', (e) => {
            if (!isDown) return;
            e.preventDefault();
            const x = e.pageX - taskScroll.offsetLeft;
            const walk = (x - startX) * 1.5;
            taskScroll.scrollLeft = scrollLeft - walk;
        });

        // 9. Active vs Lead Projects List
        const activeCard = document.getElementById('activeCard');
        const leaderCard = document.getElementById('leaderCard');

        function displayProjects(projects) {
            projectList.innerHTML = '';
            if (projects.length === 0) {
                projectList.innerHTML = '<p>No projects available</p>';
                return;
            }
            projects.forEach(project => {
                const li = document.createElement('li');
                li.classList.add('task-info-row');
                li.innerHTML = `<i class="fas fa-diagram-project"></i><span class="info-value"><a href="project.html">${project.project_name}</a></span>`;
                projectList.appendChild(li);
            });
        }

        activeCard.addEventListener("click", function () {
            displayProjects(activeProjects);
            activeCard.classList.add("active-leader");
            leaderCard.classList.remove("active-leader");
        });

        leaderCard.addEventListener("click", function () {
            displayProjects(leaderProjects);
            leaderCard.classList.add("active-leader");
            activeCard.classList.remove("active-leader");
        });

        // Load default
        displayProjects(activeProjects.length > 0 ? activeProjects : []);
    });
</script>

    </body>
</html>

