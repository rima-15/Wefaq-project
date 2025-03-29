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
async function addProjectToSidebar() {
    try {
        let response = await fetch("handleProject.php?fetch_all=true", {
            credentials: 'include' //  for sessions
        });
        
        if (!response.ok) {
            throw new Error("Network response was not ok");
        }
        let projects = await response.json();

        if (projects.error) {
            console.error("Error fetching projects:", projects.error);
            return;
        }

        const projectsList = document.getElementById("projectsList");
        if (!projectsList) return;

        // Clear existing projects but keep the "Add Project" button
        const addProjectBtn = projectsList.querySelector('.add-project-container') || 
            projectsList.querySelector('li:last-child');
        
        projectsList.innerHTML = '';
        
        // Add projects
        projects.forEach(project => {
            const li = document.createElement('li');
            li.innerHTML = `
                <a href="project.html?project_ID=${project.project_ID}" class="project-link">
                    <i class="fas fa-folder"></i>
                    <span>${project.project_name}</span>
                </a>
            `;
            projectsList.appendChild(li);
        });

        // Add the "Add Project" button back
        if (addProjectBtn) {
            projectsList.appendChild(addProjectBtn);
        } else {
            const addBtnLi = document.createElement('li');
            addBtnLi.innerHTML = `
                <button type="button" class="add-project" id="addProjectBtn">
                    <i class="fas fa-plus"></i>
                    <span>Add Project</span>
                </button>
            `;
            projectsList.appendChild(addBtnLi);
            addBtnLi.querySelector('#addProjectBtn').addEventListener('click', openProjectModal);
        }
    } catch (error) {
        console.error("Error loading projects:", error);
    }
}


document.addEventListener('DOMContentLoaded', function() {
    // Don't load components for login and signup pages
    const path = window.location.pathname;
    if (path.includes('login.php') || path.includes('signup.php')) {
        return;
    }
    
    if (window.location.pathname.includes('project.html')) {
        initializeProjectDeletion();
        updateProjectHeader();
        initializeProjectNameEditing();
        initializeProjectDescriptionEditing();
        initializeProjectCompletion(); 


    }
    
    // Then load projects
    addProjectToSidebar();
    
    initializeComponents();
    

    });
    




    // Function to initialize components
    function initializeComponents() {
        
        
        
const headerHtml = `
    <header class="dashboard-header">
        <div class="header-left">
            <button type="button" id="sidebarToggle" class="sidebar-toggle">
                <i class="fas fa-bars"></i>
            </button>
            <h1 id="pageTitle">Dashboard</h1>
            <p id="project-deadline-header"></p>
            <div class="editProject" style="display: none !important;">
<button class="btn-icon edit project-edit-btns" id="editProjectBtn" title="Edit Project" >
            <i class="fas fa-edit"></i>
        </button>
        <button class="btn-icon delete project-edit-btns" title="Delete Task" onclick="openGenericModal('deleteProjectModal')">
            <i class="fas fa-trash"></i>
        </button>            </div>
        </div>
        <div class="header-right">
            <div class="team-members" style="display: none !important;">
<div class="header-member" style="background-image: url('images/avatarF1.jpeg');"></div>
                    <div class="header-member" style="background-image: url('images/avatarM1.jpeg');"></div>
                    <div class="header-member" style="background-image: url('images/avatarF2.jpeg');"></div>                <div class="header-member add-member" style="display: none !important;" onclick="openGenericModal('invitePopup')">+</div>
            </div>
            <button class="btn btn-primary" id="complete-project-btn" style="display: none !important;" onclick="openGenericModal('completeProjectModal')">
                Mark as Complete
            </button>
            <div class="user-menu">
                <img src="images/avatarF1.jpeg" alt="User Avatar" class="user-avatar">
                <span class="user-name">Hi, John Doe</span>
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
                        <a href="dashboard.php">
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
                        <!-- Projects will be inserted here dynamically -->
                            <li class="add-project-container">
                            <button type="button" class="add-project" id="addProjectBtn">
                             <i class="fas fa-plus"></i>
                             <span>Add Project</span>
                                           </button>
                                                 </li>
                                                    </ul>
                    <li>
                        <a href="community.php">
                            <i class="fas fa-users"></i>
                            <span>Community</span>
                        </a>
                    </li>
                    <li>
                        <a href="inbox.html">
                            <i class="fas fa-inbox"></i>
                            <span>Inbox</span>
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



    // Add the modal HTML
    const modalHtml = `
        <div id="newProjectModal" class="modal">
            <div class="modal-content create-modal">
                <div class="modal-header">
                    <h2>Create New Project</h2>
                    <button class="close-modal" onclick="closeGenericModal('newProjectModal')">&times;</button>
                </div>
                <div class="modal-body">
                    <form id="newProjectForm" action="handleProject.php" method="POST">
                            <input type="hidden" name="create_project" value="1"> <!-- Hidden field -->

                    <div class="form-group">
                            <label for="projectName">Project Name</label>
                            <input type="text" id="projectName" name="project_name" required>
                        </div>
                        <div class="form-group">
                            <label for="projectDescription">Description</label>
                            <textarea id="projectDescription" required name="project_description"></textarea>
                        </div>
                        <div class="form-group">
                            <label for="projectDeadline">Deadline</label>
                            <input type="date" id="projectDeadline" name="project_deadline" required>
                        </div>
    <input type= "hidden" name="leader_ID" value="1">
                        <div id="container-btn-form">
                                       <button type="submit" class="btn btn-primary">Create Project</button>
         
</div>
                    </form>
                </div>
            </div>
        </div>
    `;
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
       /* const projectForm = document.getElementById('newProjectForm');
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
        }*/

    // Handle form submission with database
const projectForm = document.getElementById("newProjectForm");
if (projectForm) {
    projectForm.addEventListener("submit", async function (e) {
        e.preventDefault();

        try {
            let formData = new FormData(projectForm);
            let response = await fetch("handleProject.php", {
                method: "POST",
                body: formData,
                credentials: 'include' //  for sessions

            });
            
            let data = await response.json();
            
            if (data.success) {
                closeProjectModal();
                await addProjectToSidebar(); // Refresh the project list
                
                // Optionally redirect to the new project
                if (data.project_ID) {
                    window.location.href = `project.html?project_ID=${data.project_ID}`;
                }
            } else {
                alert(data.message || "Error creating project");
            }
        } catch (error) {
            console.error("Error:", error);
            alert("An error occurred while creating the project");
        }
    });
}


        // Initialize other components
        initializeSidebar();
        initializeDropdown();
        updatePageTitle();


        // Update project header if we're on the project page
        if (path.includes('project.html')) {
            const project = getProjectFromURL();
            if (!project) {
                console('Project not found!');
                window.location.href = 'dashboard.html';
                return;
            }

            updateProjectHeader(project);
            initializeProjectButtons(project);
        }
    }
async function checkLeadership(project_ID) {
    try {
        const response = await fetch(`handleProject.php?check_leadership=true&project_ID=${project_ID}`);
        const data = await response.json();
        return data.isLeader || false;
    } catch (error) {
        console.error("Leadership check failed:", error);
        return false;
    }
}
// Function to retrieve project data from URL and database
async function getProjectFromURL() {
    try {
        const urlParams = new URLSearchParams(window.location.search);
        const projectID = urlParams.get('project_ID');
        
        if (!projectID) {
            console.error('No project ID in URL');
            return null;
        }

        const response = await fetch(`handleProject.php?get_project=true&project_ID=${projectID}`);
        const data = await response.json();
        
        if (data.error) {
            console.error('Error fetching project:', data.error);
            return null;
        }

        return {
            name: data.project_name,
            description: data.project_description,
            deadline: data.project_deadline,
            status: data.status,
            project_ID: data.project_ID,
            leader_ID: data.leader_ID // Ensure this is being returned
        };
        
    } catch (error) {
        console.error("Error fetching project:", error);
        return null;
    }
}

    // Function to format the deadline
    function formatDeadline(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
    }
// update project header & exclusive btns
async function updateProjectHeader() {
    try {
        const urlParams = new URLSearchParams(window.location.search);
        const project_ID = urlParams.get('project_ID');
        
        if (!project_ID) {
            console.error('No project ID in URL');
            return;
        }

        // Get project data and leadership status in parallel
        const [project, isLeader] = await Promise.all([
            getProjectFromURL(),
            checkLeadership(project_ID)
        ]);

        if (!project) {
            console.error('Project not found!');
            return;
        }

        // Update basic project info
        document.getElementById('pageTitle').textContent = project.name;
        document.getElementById('project-deadline-header').textContent = `Deadline: ${formatDeadline(project.deadline)}`;
        document.querySelector('.project-description p').textContent = project.description || "No description available";

        // Toggle leader-only elements
        const leaderElements = [
            '.editProject',
            '#editProjectDescription',
            '.add-member',
            '#complete-project-btn'
        ];

        leaderElements.forEach(selector => {
            const el = document.querySelector(selector);
            if (el) el.style.display = isLeader ? 'flex' : 'none';
        });

        // Show team members section
        document.querySelector('.team-members').style.display = 'flex';
        document.querySelector('.user-menu').style.display = 'none';
        const isCompleted = project.status === 'completed';
        const completeBtn = document.getElementById('complete-project-btn');
        // Update project status display
        const headerRight = document.querySelector('.header-right');
        
        const existingBadge = document.querySelector('.status-badge.project-status');
        if (existingBadge) existingBadge.remove();

          if (isCompleted) {
            // Project is completed
            if (completeBtn) completeBtn.style.display = 'none';
            if (headerRight) {
                headerRight.insertAdjacentHTML('beforeend',
                    '<span class="status-badge completed">Completed</span>');
            }
        }else if (!isLeader) {
            headerRight.insertAdjacentHTML('beforeend',
                '<span class="status-badge in-progress">In Progress</span>');
        }else {
            
            completeBtn.style.display = 'flex';
        }

    } catch (error) {
        console.error("Error updating header:", error);
    }
}

    // Function to initialize project buttons
    function initializeProjectButtons(project) {
        if (!project) return;

        const editBtn = document.querySelector('.edit.project-edit-btns');
        const deleteBtn = document.querySelector('.delete.project-edit-btns');
        const addMember = document.querySelector('.add-member');
        const completeBtn = document.getElementById('complete-project-btn');

        // Delete Project Popup
        const deleteModal = document.getElementById('deleteProjectModal');
        const confirmDeleteBtn = document.getElementById('confirmDelete');
        const cancelDeleteBtn = document.getElementById('cancelDelete');

        if (deleteBtn && deleteModal && confirmDeleteBtn && cancelDeleteBtn) {
            deleteBtn.addEventListener('click', () => {
                deleteModal.style.display = 'flex';
            });

            confirmDeleteBtn.addEventListener('click', () => {
                const projects = JSON.parse(localStorage.getItem('projects') || '[]');
                const updatedProjects = projects.filter(p => p.name !== project.name);
                localStorage.setItem('projects', JSON.stringify(updatedProjects));
                window.location.href = 'dashboard.html';
            });

            cancelDeleteBtn.addEventListener('click', () => {
                deleteModal.style.display = 'none';
            });
            cancelDeleteBtn.addEventListener('click', () => {
                deleteModal.style.display = 'none';
            });
        }

        // Mark as Complete Popup
        const completeModal = document.getElementById('completeProjectModal');
        const confirmCompleteBtn = document.getElementById('confirmComplete');
        const cancelCompleteBtn = document.getElementById('cancelComplete');
        const xCompleteBtn = document.getElementById('cancelComplete');

        if (completeBtn && completeModal && confirmCompleteBtn && cancelCompleteBtn) {
            completeBtn.addEventListener('click', () => {
                completeModal.style.display = 'flex';
            });

            confirmCompleteBtn.addEventListener('click', () => {
                const projects = JSON.parse(localStorage.getItem('projects') || '[]');
                const updatedProjects = projects.map(p => {
                    if (p.name === project.name) {
                        return { ...p, status: 'Completed' };
                    }
                    return p;
                });
                localStorage.setItem('projects', JSON.stringify(updatedProjects));
                completeBtn.style.display = 'none';
                document.querySelector('.header-right').insertAdjacentHTML('beforeend', '<span class="status-badge completed">Completed</span>');
                completeModal.style.display = 'none';
            });

            cancelCompleteBtn.addEventListener('click', () => {
                completeModal.style.display = 'none';
            });

            cancelCompleteBtn.addEventListener('click', () => {
                completeModal.style.display = 'none';
            });
        }
    }

    function initializeSidebar() {
        // Create overlay if it doesn't exist
        let sidebarOverlay = document.querySelector('.sidebar-overlay');
        if (!sidebarOverlay) {
            sidebarOverlay = document.createElement('div');
            sidebarOverlay.className = 'sidebar-overlay';
            sidebarOverlay.setAttribute('data-from', 'components'); // Add data attribute
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

        const currentPage = path.split('/').pop() || 'dashboard.php';
        document.querySelectorAll('.nav-links > li > a').forEach(link => {
            if (link.getAttribute('href') === currentPage) {
                link.parentElement.classList.add('active');
            }
        });
    }
    
function initializeProjectDeletion() {
    const confirmDeleteBtn = document.getElementById('confirmProjectDelete');
    
    if (confirmDeleteBtn) {
       
        
        confirmDeleteBtn.addEventListener('click', async function() {
            const urlParams = new URLSearchParams(window.location.search);
            const project_ID = urlParams.get('project_ID');
            
            if (!project_ID) {
                alert("No project selected");
                return;
            }
            
            try {
                confirmDeleteBtn.disabled = true;
                confirmDeleteBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Deleting...';
                
                // Create FormData object
                const formData = new FormData();
                formData.append('delete_project', '1');
                formData.append('project_ID', project_ID);
                
                const response = await fetch('handleProject.php', {
                    method: 'POST',
                    body: formData,
                    credentials: 'include' // Important for session
                });
                
                const result = await response.json();
                
                if (result.success) {
                    closeGenericModal('deleteProjectModal');
                    window.location.href = 'dashboard.php?deleted=true';
                } else {
                    alert(result.message || "Failed to delete project");
                    confirmDeleteBtn.disabled = false;
                    confirmDeleteBtn.textContent = 'Delete';
                }
            } catch (error) {
                console.error("Error:", error);
                alert("An error occurred while deleting the project");
                confirmDeleteBtn.disabled = false;
                confirmDeleteBtn.textContent = 'Delete';
            }
        });
        
        // Cancel button handler remains the same
        const cancelDeleteBtn = document.getElementById('cancelDelete');
        if (cancelDeleteBtn) {
            cancelDeleteBtn.addEventListener('click', function() {
                closeGenericModal('deleteProjectModal');
            });
        }
    }
}

// Replace your current initialization with this:
function initializeProjectNameEditing() {
    console.log("Looking for edit button...");
    
    // Keep trying until elements exist (with timeout safety)
    const maxAttempts = 10;
    let attempts = 0;
    
    const checkElements = setInterval(() => {
        attempts++;
        const editBtn = document.querySelector('.edit.project-edit-btns');
        const pageTitle = document.getElementById('pageTitle');
        
        if (editBtn && pageTitle) {
            clearInterval(checkElements);
            console.log("Elements found! Setting up editor...");
            setupEditor(editBtn, pageTitle);
        } else if (attempts >= maxAttempts) {
            clearInterval(checkElements);
            console.error("Could not find elements after 10 attempts");
        }
    }, 300); // Check every 300ms
}

function setupEditor(editBtn, pageTitle) {
    editBtn.addEventListener('click', function() {
        console.log("Edit button clicked - creating input field");
        
        // Create input element
        const input = document.createElement('input');
        input.type = 'text';
        input.value = pageTitle.textContent;
        input.className = 'project-name-edit';
        
        // Style it to match your h1
        input.style.cssText = `
            font-size: inherit;
            font-weight: inherit;
            font-family: inherit;
            border: 2px solid #4a90e2;
            padding: 5px 10px;
            width: auto;
            min-width: 200px;
        `;

        // Replace h1 with input
        pageTitle.replaceWith(input);
        input.focus();

        // Handle save
        const saveEdit = async () => {
            const newName = input.value.trim();
            if (!newName) {
                alert("Project name cannot be empty");
                input.replaceWith(pageTitle);
                return;
            }

            try {
                const project_ID = new URLSearchParams(window.location.search).get('project_ID');
                const formData = new FormData();
                formData.append('update_project', '1');
                formData.append('project_ID', project_ID);
                formData.append('new_name', newName);

                const response = await fetch('handleProject.php', {
                    method: 'POST',
                    body: formData,
                    credentials: 'include'
                });
                
                const result = await response.json();
                
                if (result.success) {
                    pageTitle.textContent = newName;
                    input.replaceWith(pageTitle);
                    await addProjectToSidebar(); // Refresh sidebar
                } else {
                    throw new Error(result.message);
                }
            } catch (error) {
                console.error("Error:", error);
                alert(error.message);
                input.replaceWith(pageTitle);
            }
        };

        // Event listeners
        input.addEventListener('blur', saveEdit);
        input.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') saveEdit();
            if (e.key === 'Escape') input.replaceWith(pageTitle);
        });
    });
}
function initializeProjectDescriptionEditing() {
    console.log("Initializing description editing...");
    
    // Find elements with polling (like we did for title)
    const maxAttempts = 10;
    let attempts = 0;
    
    const checkElements = setInterval(() => {
        attempts++;
        const editBtn = document.getElementById('editProjectDescription');
        const descriptionElement = document.getElementById('project-description-content');
        
        if (editBtn && descriptionElement) {
            clearInterval(checkElements);
            console.log("Description elements found!");
            setupDescriptionEditor(editBtn, descriptionElement);
        } else if (attempts >= maxAttempts) {
            clearInterval(checkElements);
            console.error("Couldn't find description elements");
        }
    }, 300);
}
function setupDescriptionEditor(editBtn, descriptionElement) {
    editBtn.addEventListener('click', function() {
        // Create textarea
        const textarea = document.createElement('textarea');
        textarea.value = descriptionElement.textContent;
        textarea.className = 'project-description-edit';
        textarea.style.cssText = `
            width: 100%;
            min-height: 100px;
            padding: 10px;
            border: 2px solid #4a90e2;
            border-radius: 4px;
            font-family: inherit;
            font-size: inherit;
        `;

        // Replace paragraph with textarea
        descriptionElement.replaceWith(textarea);
        textarea.focus();

        // Handle save
        const saveEdit = async () => {
            const newDescription = textarea.value.trim();
            const project_ID = new URLSearchParams(window.location.search).get('project_ID');
            
            try {
                const formData = new FormData();
                formData.append('update_description', '1');
                formData.append('project_ID', project_ID);
                formData.append('new_description', newDescription);

                const response = await fetch('handleProject.php', {
                    method: 'POST',
                    body: formData,
                    credentials: 'include'
                });
                
                const result = await response.json();
                
                if (result.success) {
                    descriptionElement.textContent = newDescription;
                    textarea.replaceWith(descriptionElement);
                } else {
                    throw new Error(result.message || "Failed to update description");
                }
            } catch (error) {
                console.error("Update error:", error);
                alert(error.message);
                textarea.replaceWith(descriptionElement);
            }
        };

        // Event listeners
        textarea.addEventListener('blur', saveEdit);
        textarea.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' && e.ctrlKey) saveEdit();
            if (e.key === 'Escape') textarea.replaceWith(descriptionElement);
        });
    });
}

function initializeProjectCompletion() {
    const completeBtn = document.getElementById('confirmCompleteProject');
    
    if (completeBtn) {
        completeBtn.addEventListener('click', async function() {
            try {
                completeBtn.disabled = true;
                completeBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Completing...';
                
                const project_ID = new URLSearchParams(window.location.search).get('project_ID');
                const response = await fetch('handleProject.php', {
                    method: 'POST',
                    body: new URLSearchParams({
                        complete_project: '1',
                        project_ID: project_ID
                    }),
                    credentials: 'include'
                });
                
                const result = await response.json();
                
                if (result.success) {
                    closeGenericModal('completeProjectModal');
                    await updateProjectHeader(); // Use the main header update function
                } else {
                    throw new Error(result.message || "Failed to complete project");
                }
            } catch (error) {
                console.error("Completion error:", error);
                alert(error.message);
            } finally {
                completeBtn.disabled = false;
                completeBtn.textContent = 'Complete';
            }
        });
    }
}