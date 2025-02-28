// Make these functions global
window.openProjectModal = function() {
    const modal = document.getElementById('newProjectModal');
    if (modal) {
        modal.style.display = 'block';
        document.body.style.overflow = 'hidden';
    }
};

window.closeProjectModal = function() {
    const modal = document.getElementById('newProjectModal');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = '';
        const form = document.getElementById('newProjectForm');
        if (form) form.reset();
    }
};

// Function to add project to sidebar
function addProjectToSidebar(project) {
    const projectsList = document.getElementById('projectsList');
    if (!projectsList) return;

    const projectItem = document.createElement('li');
    projectItem.innerHTML = `
        <a href="project.html?name=${encodeURIComponent(project.name)}" class="project-link">
            <i class="fas fa-folder"></i>
            <span>${project.name}</span>
        </a>
    `;
    
    // Insert before the "Add Project" button
    const addProjectBtn = projectsList.querySelector('.add-project').parentElement;
    projectsList.insertBefore(projectItem, addProjectBtn);
}

document.addEventListener('DOMContentLoaded', function() {
    // Don't load components for login and signup pages
    const path = window.location.pathname;
    if (path.includes('login.html') || path.includes('signup.html')) {
        return;
    }

    // Add the header HTML directly
    const headerHtml = `
        <header class="dashboard-header">
            <div class="header-left">
                <button type="button" id="sidebarToggle" class="sidebar-toggle">
                    <i class="fas fa-bars"></i>
                </button>
                <h1 id="pageTitle">Dashboard</h1>
            </div>
            <div class="header-right">
                <div class="user-menu">
                    <img src="Wefaq.jpg" alt="User Avatar" class="user-avatar">
                    <span class="user-name">John Doe</span>
                </div>
            </div>
        </header>
    `;

    // Add the sidebar HTML directly
    const sidebarHtml = `
        <nav class="sidebar" id="sidebar">
            <div class="logo">
                <img src="Wefaq.jpg" alt="Wefaq Logo" class="logo-img">
            </div>
            <div class="nav-content">
                <ul class="nav-links">
                    <li>
                        <a href="dashboard.html">
                            <i class="fas fa-home"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li class="projects-item">
                        <a href="#" id="projectsDropdown">
                            <div class="menu-item">
                                <i class="fas fa-project-diagram"></i>
                                <span>Projects</span>
                            </div>
                            <i class="fas fa-chevron-down dropdown-icon"></i>
                        </a>
                        <ul class="projects-submenu" id="projectsList">
                            ${loadProjectsFromStorage()}
                            <li>
                                <button type="button" class="add-project" id="addProjectBtn">
                                    <i class="fas fa-plus"></i>
                                    <span>Add Project</span>
                                </button>
                            </li>
                        </ul>
                    </li>
                    <li>
                        <a href="community.html">
                            <i class="fas fa-users"></i>
                            <span>Community</span>
                        </a>
                    </li>
                    <li>
                        <a href="profile.html">
                            <i class="fas fa-user"></i>
                            <span>Profile</span>
                        </a>
                    </li>
                </ul>
                <div class="nav-bottom">
                    <a href="index.html" class="logout-btn">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Log Out</span>
                    </a>
                </div>
            </div>
        </nav>
    `;

    // Function to load projects from localStorage
    function loadProjectsFromStorage() {
        const projects = JSON.parse(localStorage.getItem('projects') || '[]');
        return projects.map(project => `
            <li>
                <a href="project.html?name=${encodeURIComponent(project.name)}" class="project-link">
                    <i class="fas fa-folder"></i>
                    <span>${project.name}</span>
                </a>
            </li>
        `).join('');
    }

    // Add the modal HTML
    const modalHtml = `
        <div id="newProjectModal" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h2>Create New Project</h2>
                    <button class="close-modal">&times;</button>
                </div>
                <div class="modal-body">
                    <form id="newProjectForm">
                        <div class="form-group">
                            <label for="projectName">Project Name</label>
                            <input type="text" id="projectName" required>
                        </div>
                        <div class="form-group">
                            <label for="projectDescription">Description</label>
                            <textarea id="projectDescription" required></textarea>
                        </div>
                        <div class="form-group">
                            <label for="projectDeadline">Deadline</label>
                            <input type="date" id="projectDeadline" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Create Project</button>
                    </form>
                </div>
            </div>
        </div>
    `;

    // Function to initialize components
    function initializeComponents() {
        // Insert components
        const container = document.querySelector('.dashboard-container');
        if (!container) {
            console.error('Container not found');
            return;
        }

        const mainContent = document.querySelector('.main-content');
        if (!mainContent) {
            console.error('Main content not found');
            return;
        }

        // Insert the components
        container.insertAdjacentHTML('afterbegin', sidebarHtml);
        mainContent.insertAdjacentHTML('afterbegin', headerHtml);
        
        // Only add modal if it doesn't exist
        if (!document.getElementById('newProjectModal')) {
            document.body.insertAdjacentHTML('beforeend', modalHtml);
        }

        // Add event listeners for modal
        const addProjectBtn = document.getElementById('addProjectBtn');
        if (addProjectBtn) {
            addProjectBtn.addEventListener('click', function(e) {
                e.preventDefault();
                openProjectModal();
            });
        }

        const closeBtn = document.querySelector('.close-modal');
        if (closeBtn) {
            closeBtn.addEventListener('click', closeProjectModal);
        }

        // Close modal when clicking outside
        window.addEventListener('click', function(e) {
            const modal = document.getElementById('newProjectModal');
            if (e.target === modal) {
                closeProjectModal();
            }
        });

        // Close modal when pressing Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeProjectModal();
            }
        });

        // Handle form submission
        const projectForm = document.getElementById('newProjectForm');
        if (projectForm) {
            projectForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const projectData = {
                    name: document.getElementById('projectName').value,
                    description: document.getElementById('projectDescription').value,
                    deadline: document.getElementById('projectDeadline').value
                };

                // Add project to localStorage
                const projects = JSON.parse(localStorage.getItem('projects') || '[]');
                projects.push(projectData);
                localStorage.setItem('projects', JSON.stringify(projects));

                // Add to sidebar and redirect
                addProjectToSidebar(projectData);
                closeProjectModal();
                window.location.href = `project.html?name=${encodeURIComponent(projectData.name)}`;
            });
        }

        // Initialize other components
        initializeSidebar();
        initializeDropdown();
        updatePageTitle();

        // Update project title if we're on the project page
        if (path.includes('project.html')) {
            const urlParams = new URLSearchParams(window.location.search);
            const projectName = urlParams.get('name');
            if (projectName) {
                const pageTitle = document.getElementById('pageTitle');
                if (pageTitle) {
                    pageTitle.textContent = decodeURIComponent(projectName);
                }
            }
        }
    }

    function initializeSidebar() {
        // Create overlay if it doesn't exist
        let sidebarOverlay = document.querySelector('.sidebar-overlay');
        if (!sidebarOverlay) {
            sidebarOverlay = document.createElement('div');
            sidebarOverlay.className = 'sidebar-overlay';
            document.body.appendChild(sidebarOverlay);
        }

        const sidebar = document.getElementById('sidebar');
        const sidebarToggle = document.getElementById('sidebarToggle');

        if (sidebar && sidebarToggle) {
            function toggleSidebar(e) {
                if (e) {
                    e.preventDefault();
                    e.stopPropagation();
                }
                sidebar.classList.toggle('active');
                sidebarOverlay.classList.toggle('active');
                document.body.style.overflow = sidebar.classList.contains('active') ? 'hidden' : '';
            }

            sidebarToggle.addEventListener('click', toggleSidebar);
            sidebarToggle.addEventListener('mousedown', (e) => e.preventDefault());
            sidebarOverlay.addEventListener('click', toggleSidebar);
        }
    }

    function initializeDropdown() {
        const projectsDropdown = document.getElementById('projectsDropdown');
        const projectsList = document.getElementById('projectsList');
        const dropdownIcon = projectsDropdown?.querySelector('.dropdown-icon');

        if (projectsDropdown && projectsList && dropdownIcon) {
            function toggleDropdown(e) {
                e.preventDefault();
                e.stopPropagation();
                projectsList.classList.toggle('show');
                dropdownIcon.classList.toggle('open');
            }

            projectsDropdown.addEventListener('click', toggleDropdown);

            document.addEventListener('click', function(e) {
                if (!projectsDropdown.contains(e.target) && !projectsList.contains(e.target)) {
                    projectsList.classList.remove('show');
                    dropdownIcon.classList.remove('open');
                }
            });
        }
    }

    function updatePageTitle() {
        const pageName = document.title.split('-')[0].trim();
        const pageTitle = document.getElementById('pageTitle');
        if (pageTitle) {
            pageTitle.textContent = pageName;
        }

        const currentPage = path.split('/').pop() || 'dashboard.html';
        document.querySelectorAll('.nav-links > li > a').forEach(link => {
            if (link.getAttribute('href') === currentPage) {
                link.parentElement.classList.add('active');
            }
        });
    }

    // Initialize components
    initializeComponents();
});
