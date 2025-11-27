<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Registration</title>
    <!-- Tailwind CSS (for modern, responsive styling) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Custom styles to ensure 'Inter' font and smooth appearance */
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f0f4f8; /* Light background for registration */
        }
        .register-card {
            box-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        .input-field {
            transition: all 0.3s;
        }
        .input-field:focus {
            outline: none;
            border-color: #10b981; /* Tailwind emerald-500 */
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.5);
        }
        /* Custom style for file input focus */
        #profile_image:focus + label {
            border-color: #10b981;
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.5);
        }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen p-4">

    <div class="w-full max-w-lg">
        <!-- Registration Card -->
        <div class="register-card bg-white p-6 sm:p-8 rounded-xl space-y-6">
            <h1 class="text-3xl font-bold text-gray-800 text-center">Create Account</h1>
            <p class="text-gray-500 text-center">Join us and start managing your charges.</p>

            <form id="register-form" class="space-y-4">
                <!-- Status Message Area (for errors/success) -->
                <div id="status-message" class="hidden p-3 rounded-lg text-sm font-medium"></div>

                <!-- Name Input -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                    <input type="text" id="name" name="name" required placeholder="John Doe"
                           class="input-field w-full px-4 py-2 border border-gray-300 rounded-lg">
                </div>
                
                <!-- Email Input -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                    <input type="email" id="email" name="email" required placeholder="you@example.com"
                           class="input-field w-full px-4 py-2 border border-gray-300 rounded-lg">
                </div>

                <!-- Phone Input -->
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                    <input type="tel" id="phone" name="phone" required placeholder="+1234567890"
                           class="input-field w-full px-4 py-2 border border-gray-300 rounded-lg">
                </div>

                <!-- Password Input -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                    <input type="password" id="password" name="password" required placeholder="••••••••"
                           class="input-field w-full px-4 py-2 border border-gray-300 rounded-lg">
                </div>

                <!-- Password Confirmation Input -->
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" required placeholder="••••••••"
                           class="input-field w-full px-4 py-2 border border-gray-300 rounded-lg">
                </div>

                <!-- Profile Image Input -->
                <div>
                    <label for="profile_image" class="block text-sm font-medium text-gray-700 mb-1">Profile Image (Optional)</label>
                    <!-- Visually hidden input for file upload -->
                    <input type="file" id="profile_image" name="profile_image" accept="image/*" class="sr-only" onchange="updateFileName(this)">
                    
                    <!-- Custom button/label for file input -->
                    <label for="profile_image" id="file-label"
                           class="cursor-pointer flex items-center justify-between w-full px-4 py-2 border-2 border-dashed border-gray-300 rounded-lg text-gray-500 hover:border-emerald-500 hover:text-emerald-600 transition duration-150 ease-in-out">
                        <span>Upload Profile Picture (Max 1MB)</span>
                        <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                    </label>
                    <p id="file-name" class="mt-1 text-sm text-gray-500"></p>
                </div>
                
                <!-- Submit Button -->
                <button type="submit" id="register-button"
                        class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-lg font-semibold text-white bg-emerald-600 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 transition duration-150 ease-in-out">
                    Register
                </button>
            </form>
        </div>
        
        <!-- Footer Link -->
        <p class="mt-6 text-center text-sm text-gray-600">
            Already have an account?
            <a href="{{ route('login') }}" class="font-medium text-blue-600 hover:text-blue-500">Sign in here</a>
        </p>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.getElementById('register-form');
            const statusMessage = document.getElementById('status-message');
            const registerButton = document.getElementById('register-button');
            const baseUrl = window.location.origin;

            // --- Configuration (UPDATED) ---
            const API_URL = `${baseUrl}//api/register`;
            // Redirect to the login page after successful registration
            const REDIRECT_URL = '/login-form'; 
            const MAX_RETRIES = 3;

            // Helper to update the displayed file name
            window.updateFileName = (input) => {
                const fileNameDisplay = document.getElementById('file-name');
                const fileLabel = document.getElementById('file-label').querySelector('span');
                if (input.files.length > 0) {
                    fileLabel.textContent = `File selected: ${input.files[0].name}`;
                    fileNameDisplay.textContent = input.files[0].name;
                } else {
                    fileLabel.textContent = 'Upload Profile Picture (Max 1MB)';
                    fileNameDisplay.textContent = '';
                }
            };

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
                registerButton.disabled = isLoading;
                if (isLoading) {
                    registerButton.innerHTML = '<svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Registering...';
                } else {
                    registerButton.innerHTML = 'Register';
                }
            };

            // --- API Call with Exponential Backoff ---
            async function attemptRegistration(formData) {
                let delay = 1000; // 1 second
                setLoadingState(true);

                for (let i = 0; i < MAX_RETRIES; i++) {
                    try {
                        const response = await fetch(API_URL, {
                            method: 'POST',
                            headers: { 'Accept': 'application/json' },
                            body: formData
                        });
                        
                        // Handle success (200-299)
                        if (response.ok) {
                            const data = await response.json();
                            
                            // Combine Token and Token Type for Authorization Header
                            if (data.token && data.token_type) {
                                const fullToken = `${data.token_type} ${data.token}`;
                                
                                // Store the combined token
                                // NOTE: The provided code stores the token, but since we are redirecting to login, 
                                // this token is now irrelevant, as the user will need to explicitly log in.
                                // I am leaving this line for parity with your original code structure.
                                localStorage.setItem('auth_token', fullToken);
                                console.log(`Authorization Token Stored: ${fullToken}`);
                            }
                            
                            // UPDATED SUCCESS MESSAGE AND REDIRECT
                            showMessage('Registration successful! Redirecting to the login page...', 'success');
                            
                            // Redirect after a brief delay
                            setTimeout(() => {
                                window.location.href = REDIRECT_URL; 
                            }, 500);
                            return; // Stop the loop and function execution
                        } 
                        
                        // Handle failure responses (e.g., 401 Unauthorized, 422 Validation Error)
                        const errorData = await response.json();

                        if (response.status === 422 && errorData.errors) {
                             // Handle Laravel validation errors and display the first one found
                            const firstErrorKey = Object.keys(errorData.errors)[0];
                            const errorMessage = errorData.errors[firstErrorKey][0];
                            showMessage(`Validation Error: ${errorMessage}`, 'error');
                            break; 
                        } else if (errorData.message) {
                             // General API error message
                            showMessage(errorData.message, 'error');
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

                // Create FormData object from the form
                const formData = new FormData(form);
                
                // Important: Ensure password and confirmation match client-side before sending
                const password = document.getElementById('password').value;
                const passwordConfirmation = document.getElementById('password_confirmation').value;

                if (password !== passwordConfirmation) {
                    showMessage('Password and confirmation do not match.');
                    return;
                }

                // Call the registration function
                attemptRegistration(formData);
            });
        });
    </script>
</body>
</html>