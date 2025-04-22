// API base URL
const API_BASE_URL = 'http://localhost/project2';

// API endpoints
const API_ENDPOINTS = {
    login: '/auth.php',
    signup: '/auth.php',
    logout: '/auth.php?action=logout',
    stories: '/stories.php',
    story: '/stories.php'
};

// API call function
async function apiCall(endpoint, method = 'GET', data = null) {
    const url = API_BASE_URL + endpoint;
    console.log('Making API call to:', url);

    const options = {
        method,
        headers: {
            'Content-Type': 'application/json'
        }
    };

    if (data) {
        options.body = JSON.stringify(data);
        console.log('Request data:', data);
    }

    try {
        const response = await fetch(url, options);
        console.log('Response status:', response.status);
        
        const result = await response.json();
        console.log('Response data:', result);
        return result;
    } catch (error) {
        console.error('API Error:', error);
        throw error;
    }
}

// Auth functions
async function signup(name, email, password) {
    return apiCall(API_ENDPOINTS.signup, 'POST', {
        action: 'signup',
        name,
        email,
        password
    });
}

async function login(email, password) {
    return apiCall(API_ENDPOINTS.login, 'POST', {
        action: 'login',
        email,
        password
    });
}

async function logout() {
    return apiCall(API_ENDPOINTS.logout);
}

// Story functions
async function getStories(filters = {}) {
    const queryParams = new URLSearchParams(filters);
    return apiCall(`${API_ENDPOINTS.stories}?${queryParams}`);
}

async function getStory(id) {
    return apiCall(`${API_ENDPOINTS.story}?id=${id}`);
}

async function saveProgress(storyId, progress) {
    return apiCall(API_ENDPOINTS.stories, 'POST', {
        action: 'save_progress',
        story_id: storyId,
        progress
    });
}

// Export functions
window.StoryLandAPI = {
    signup,
    login,
    logout,
    getStories,
    getStory,
    saveProgress
}; 