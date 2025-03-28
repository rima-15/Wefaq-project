<?php

/* 
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/EmptyPHP.php to edit this template
 */

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home Page - Wefaq - Project Management</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/simplebar/dist/simplebar.min.css">
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="script.js"></script>

    
</head>
<body>
   <div class="dashboard-container">
        <main class="main-content">
            <!-- Header will be loaded here -->
            <div class="partsDash">
            <div class="profile-card">
                <!-- Top Section (Purple Background) -->
                <div class="profile-header">
                    <div class="avatar">
                        <i class="fas fa-user"></i>
                    </div>
                </div>

                <!-- Profile Content -->
                <div class="profile-content">
                    <h2>John Doe</h2>
                    <p><i class="fas fa-user-graduate"></i> <strong>Type:</strong> Professional</p>
                    <p><i class="fas fa-university"></i> <strong>Organization:</strong> Saudi Electricity Company</p>
                </div>

                <!-- Skills Section -->
                <div class="skills-container">
                    <h3><i class="fas fa-tools"></i> Skills</h3>
                    <div class="skill">
                        <span><i class="fas fa-code"></i> Programming</span>
                        <div class="progress"><div class="fill" style="width: 70%;"></div></div>
                    </div>
                    <div class="skill">
                        <span><i class="fas fa-paint-brush"></i> Design</span>
                        <div class="progress"><div class="fill" style="width: 50%;"></div></div>
                    </div>
                    <div class="skill">
                        <span><i class="fas fa-clock"></i> Time Management</span>
                        <div class="progress"><div class="fill" style="width: 80%;"></div></div>
                    </div>
                </div>

                <!-- Footer with Button -->
                <div class="profile-footer">
                   <button onclick="window.location.href='profile.html'">
    <i class="fas fa-eye"></i> View Profile
</button>

                </div>
            </div>

            <div class="projects-tasks">
                <div class="active-projects" data-simplebar>
                    <h2>Active Projects</h2>
                    <div class="projects-container">
                        <div class="project-item">
                            <div class="logo-background">
                                <img src="Wefaq2.png" alt="Data Science Logo" style="width: 90%;height: 60%;">
                            </div>
                            <a href="project.html" class="Active-Project-Name">Wefaq</a>
                        </div>
    
                        <div class="project-item">
                            <div class="logo-background">
                                <img src="Network2.png" alt="Network Logo" style="width: 100%;height: 100%;">
                            </div>
                            <a href="project.html" class="Active-Project-Name">Network</a>
                        </div>
        
                        <div class="project-item">
                            <div class="logo-background">
                                <img src="DSCC.png" alt="Network Logo" style="width: 90%;height: 60%;">
                            </div>
                            <a href="project.html" class="Active-Project-Name">Data Science</a>
                        </div>
        
                        <div class="project-item">
                            <div class="logo-background">
                                <img src="Fekra.png" alt="Network Logo" style="width: 100%;height: 100%;">
                            </div>
                            <a href="project.html" class="Active-Project-Name">فكرة</a>
                        </div>
        
                        <div class="project-item">
                            <div class="logo-background">
                                <img src="R.png" alt="Network Logo" style="width: 100%;height: 100%;">
                            </div>
                            <a href="project.html" class="Active-Project-Name">رؤية</a>
                        </div>
        
                        <!-- Add more project items as needed -->
                    </div>
                </div>
            
           
                <div class="my-tasks">
                    <h2>My Tasks</h2>
                    <ul class="task-list">
                        <li class="task-item">
                            <i class="fas fa-project-diagram icon"></i>
                            <a href="project.html" class="project-link">Network</a>
                            <span class="separator">|</span>
                            <i class="fas fa-tasks icon"></i>
                            <span class="task-name">Create Logo</span>
                            <span class="separator">|</span>
                            <i class="far fa-clock icon"></i>
                            <span class="task-deadline">21/03/2025</span>
                        </li>
                        <li class="task-item">
                            <i class="fas fa-project-diagram icon"></i>
                            <a href="project.html" class="project-link">Data Science</a>
                            <span class="separator">|</span>
                            <i class="fas fa-tasks icon"></i>
                            <span class="task-name">Data Preprocessing </span>
                            <span class="separator">|</span>
                            <i class="far fa-clock icon"></i>
                            <span class="task-deadline">2/04/2025</span>
                        </li>
                        
                        <li class="task-item">
                            <i class="fas fa-project-diagram icon"></i>
                            <a href="project.html" class="project-link">Wefaq</a>
                            <span class="separator">|</span>
                            <i class="fas fa-tasks icon"></i>
                            <span class="task-name">Activity Diagram </span>
                            <span class="separator">|</span>
                            <i class="far fa-clock icon"></i>
                            <span class="task-deadline">2/04/2025</span>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="container">
                <aside class="calendar">
                    <div class="calendar-header">
                        <button onclick="prevMonth()">&#9665;</button>
                        <span id="calendar-header"></span>
                        <button onclick="nextMonth()">&#9655;</button>
                    </div>
                    <div class="calendar-grid" id="weekday-labels"></div>
                    <div class="calendar-grid" id="calendar-grid"></div>
                    <hr>
                    <h3 class="event-title">Upcoming Events</h3>
                    <div class="event"> 
                        <div class="event-circle">20</div>
                        <span>Design Phase</span>
                    </div>
                    <div class="event">
                        <div class="event-circle">25</div>
                        <span>Development</span>
                    </div>
                    <div class="event">
                        <div class="event-circle">30</div>
                        <span>Project Launch</span>
                    </div>
    
                </aside>
            
            </div>
           </div>
        </main>   
    </div>
    <script src="script.js"></script>
    <script src="js/components.js"></script>
    <script>
        const deadlines = {
            20: { type: 'task', name: 'Design Phase' },
            25: { type: 'task', name: 'Development' },
            30: { type: 'project', name: 'Project Launch' }
        };
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
                
                if (deadlines[day]) {
                    dayCell.classList.add(deadlines[day].type === 'task' ? 'task-deadline' : 'project-deadline');
                    dayCell.setAttribute('data-task', deadlines[day].name);
                }
                
                calendarGrid.appendChild(dayCell);
            }
        }
        
        function prevMonth() {
            currentMonth--;
            if (currentMonth < 0) {
                currentMonth = 11;
                currentYear--;
            }
            generateCalendar(currentMonth, currentYear);
        }
        
        function nextMonth() {
            currentMonth++;
            if (currentMonth > 11) {
                currentMonth = 0;
                currentYear++;
            }
            generateCalendar(currentMonth, currentYear);
        }
        
        generateCalendar(currentMonth, currentYear);
    </script>
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
</body>
</html>

        