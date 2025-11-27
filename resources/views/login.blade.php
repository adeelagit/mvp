<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Login</title>
    <!-- Tailwind CSS (for modern, responsive styling) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Custom styles to ensure 'Inter' font and smooth appearance */
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f7f9fb;
        }
        .login-card {
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            transition: transform 0.3s ease;
        }
        .login-card:hover {
            transform: translateY(-2px);
        }
        .input-field {
            transition: all 0.3s;
        }
        .input-field:focus {
            outline: none;
            border-color: #3b82f6; /* Tailwind blue-500 */
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.5);
        }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen p-4">

    <div class="w-full max-w-md">
        <!-- Login Card -->
        <div id="login-card" class="login-card bg-white p-8 rounded-xl space-y-6">
            <h1 class="text-3xl font-bold text-gray-800 text-center">Welcome Back</h1>
            <p class="text-gray-500 text-center">Sign in to access your dashboard.</p>

            <form id="login-form" class="space-y-6">
                <!-- Status Message Area (for errors/success) -->
                <div id="status-message" class="hidden p-3 rounded-lg text-sm font-medium"></div>

                <!-- Email Input -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                    <input type="email" id="email" required placeholder="you@example.com"
                           class="input-field w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                </div>

                <!-- Password Input -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                    <input type="password" id="password" required placeholder="••••••••"
                           class="input-field w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                </div>

                <!-- Submit Button -->
                <button type="submit" id="login-button"
                        class="w-full flex justify-center py-2 px-4 border border-transparent rounded-lg shadow-sm text-lg font-semibold text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-150 ease-in-out">
                    Sign In
                </button>
            </form>
        </div>
        
        <!-- Footer Link -->
        <p class="mt-6 text-center text-sm text-gray-600">
            Don't have an account?
            <a href="{{ route('user.register') }}" class="font-medium text-blue-600 hover:text-blue-500">Register Now</a>
        </p>
    </div>

    <script>
        // Simple in-memory object to simulate session storage for auth data
        const sessionStore = {}; 
        const baseUrl = window.location.origin;
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.getElementById('login-form');
            const emailInput = document.getElementById('email');
            const passwordInput = document.getElementById('password');
            const statusMessage = document.getElementById('status-message');
            const loginButton = document.getElementById('login-button');

            // --- Configuration ---
            const API_URL = `${baseUrl}/api/login`;
            // The route name specified is 'admin.dashboard', we use the relative path
            const REDIRECT_URL = '/admin/dashboard'; 
            const MAX_RETRIES = 3;

            // --- Utility Function for Status Messages ---
            const showMessage = (message, type = 'error') => {
                statusMessage.textContent = message;
                statusMessage.classList.remove('hidden', 'bg-red-100', 'text-red-700', 'bg-green-100', 'text-green-700');
                
                if (type === 'success') {
                    statusMessage.classList.add('bg-green-100', 'text-green-700');
                } else { // default to error
                    statusMessage.classList.add('bg-red-100', 'text-red-700');
                }
            };

            const hideMessage = () => {
                statusMessage.classList.add('hidden');
                statusMessage.textContent = '';
            };
            
            // Sets the loading state on the button
            const setLoadingState = (isLoading) => {
                loginButton.disabled = isLoading;
                if (isLoading) {
                    loginButton.innerHTML = '<svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Signing In...';
                } else {
                    loginButton.innerHTML = 'Sign In';
                }
            };

            // --- API Call with Exponential Backoff ---
            async function attemptLogin(email, password) {
                let delay = 1000; // 1 second
                setLoadingState(true);

                for (let i = 0; i < MAX_RETRIES; i++) {
                    try {
                        const response = await fetch(API_URL, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json' 
                            },
                            body: JSON.stringify({ email, password })
                        });
                        
                        // Handle success (200-299)
                        if (response.ok) {
                            const data = await response.json();
                            
                            // 1. Combine Token and Token Type for Authorization Header
                            if (data.token && data.token_type) {
                                const fullToken = `${data.token_type} ${data.token}`;
                                
                                // 2. Store the combined token (using the sessionStore object)
                                // If the environment supported Firebase, we would use it here.
                                // For this simulation, we use localStorage as the user requested it initially.
                                localStorage.setItem('auth_token', fullToken);
                                
                                console.log(`Authorization Token Stored: ${fullToken}`);
                            } else {
                                console.warn('Login successful, but token or token_type not found in API response.');
                            }
                            
                            showMessage('Login successful! Redirecting to dashboard...', 'success');
                            
                            // 3. Redirect after a brief delay to the correct route
                            setTimeout(() => {
                                window.location.href = REDIRECT_URL; 
                            }, 500);
                            return; // Stop the loop and function execution
                        } 
                        
                        // Handle failure responses (e.g., 401 Unauthorized, 422 Validation Error)
                        const errorData = await response.json();
                        if (response.status === 401 || response.status === 422) {
                            // Show the specific error message returned by the API
                            const errorMessage = errorData.message || 'Invalid email or password.';
                            showMessage(errorMessage, 'error');
                            // Do not retry on credential/validation errors
                            break; 
                        }

                        // For other server errors (e.g., 500), throw to trigger retry
                        throw new Error(`Server responded with status: ${response.status}`);

                    } catch (error) {
                        // Network error (e.g., server is down) or thrown error from above
                        console.error(`Attempt ${i + 1} failed:`, error.message);
                        
                        if (i < MAX_RETRIES - 1) {
                            // Apply exponential backoff before the next retry
                            await new Promise(resolve => setTimeout(resolve, delay));
                            delay *= 2; 
                        } else {
                            // Last attempt failed
                            showMessage('Failed to connect to the server after multiple retries. Please check your network.', 'error');
                        }
                    }
                }
                
                // Reset button state if login was not successful
                setLoadingState(false);
            }

            // --- Form Submission Handler ---
            form.addEventListener('submit', (e) => {
                e.preventDefault();
                hideMessage();

                const email = emailInput.value.trim();
                const password = passwordInput.value;
                
                if (!email || !password) {
                    showMessage('Please enter both email and password.');
                    return;
                }
                
                // Call the login function
                attemptLogin(email, password);
            });
        });
    </script>
</body>
</html>