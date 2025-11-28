<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


<script>
    // --- MOCK DATA FOR PREVIEW/FALLBACK (rest of the mockStore remains the same) ---
    const mockStore = {
        users: [
            { id: 1, name: 'Zahraa', email: 'zahraa18@gmail.com', phone: '+1234567893', role: 'User', joined: '2025-02-15' },
            { id: 2, name: 'John Doe', email: 'john10@example.com', phone: '+9876543210', role: 'User', joined: '2025-02-18' }
        ],
        types: [
            { id: 1, name: 'Car', icon: 'fa-car' },
            { id: 2, name: 'Bike', icon: 'fa-motorcycle' }
        ],
        brands: [
            { id: 1, type_id: 1, name: 'Tesla', logo: 'https://upload.wikimedia.org/api/commons/e/e8/Tesla_logo.png', models: ['Model 3', 'Model Y'] },
            { id: 2, type_id: 2, name: 'Ather', logo: 'https://upload.wikimedia.org/api/commons/2/2a/Ather_Energy_Logo.svg', models: ['450X'] }
        ],
        vehicles: [
            { id: 101, ownerId: 2, typeId: 1, brandId: 1, model: 'Model 3', plate: 'KA09XX1234', color: 'White', year: 2024, type: 'Car' }
        ],
        tickets: [
            { id: 1, userId: 1, category: 'Low Battery', desc: 'Stuck near central mall', status: 'Pending', date: '2025-02-20' }
        ],
        plates: [
            { id: 1, number: 'KA 05 AB 1234', img: '' }
        ]
    };

    // --- DATA STORE (Initialized empty) ---
    let store = {
        users: [], types: [], brands: [], vehicles: [], tickets: [], plates: []
    };

    // --- HELPER FOR API CALLS ---
    const api = {
        getHeaders: () => {
            const token = localStorage.getItem('auth_token'); // Get token from local storage
            const headers = {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                // Safe check for meta tag
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            };

            if (token) {
                headers['Authorization'] = `Bearer ${token}`;
            }
            return headers;
        },
        get: async (url) => (await fetch(url, { headers: api.getHeaders() })).json(),
        //post: async (url, data) => (await fetch(url, { method: 'POST', headers: api.getHeaders(), body: JSON.stringify(data) })).json(),
        post: async (url, data) => {
            let options = { method: 'POST' };

            // If FormData, do NOT send JSON header
            if (data instanceof FormData) {
                options.body = data;
                options.headers = api.getHeaders();
                delete options.headers['Content-Type']; // remove JSON header
            } else {
                options.body = JSON.stringify(data);
                options.headers = api.getHeaders();
            }

            return (await fetch(url, options)).json();
        },
        delete: async (url) => (await fetch(url, { method: 'DELETE', headers: api.getHeaders() })).json()
    };
    
    // --- DOM HELPER (unchanged) ---
    const dom = {
        setText: (id, val) => {
            const el = document.getElementById(id);
            if(el) el.innerText = val;
        },
        setHtml: (id, html) => {
            const el = document.getElementById(id);
            if(el) el.innerHTML = html;
        }
    };

    // --- APP LOGIC (unchanged) ---
    const app = {
        logout: async () => {
    try {
        await api.post('/api/logout'); // calls Laravel logout
    } catch(e) {
        console.warn('Logout failed or offline mode');
    }

    // remove stored token
    localStorage.removeItem('auth_token');

    // redirect to login page (or reload)
    window.location.href = '/login-form';
},
        init: async () => {
            await app.data.loadAll(); // Load data from Laravel or Fallback
            await app.data.loadTypes();
            await app.data.loadPlates();
            await app.data.loadBrands();
            await app.data.loadUsers();
            await app.data.loadVehicles();
            await app.data.loadTickets();

            // Render all sections safely
            app.render.dashboard();
            app.render.tickets();
            app.render.users();
            app.render.types();
            app.render.brands();
            app.render.vehicles();
            app.render.plates();
            
            const sidebarToggle = document.getElementById('sidebarToggle');
            if(sidebarToggle) {
                sidebarToggle.addEventListener('click', () => {
                    document.getElementById('sidebar')?.classList.toggle('show');
                });
            }
        },

        data: {
            loadBrands: async () => {
                try {
                    const response = await api.get('/api/brands');
                    const brandsArray = [];

                    Object.keys(response).forEach(category => {
                        response[category].forEach(b => {
                            brandsArray.push({
                                id: b.id,
                                type_id: 1, // Adjust if you have a type mapping
                                name: b.name,
                                logo: b.logo,
                                models: (b.submodels || []).map(sm => sm.submodel_name)
                            });
                        });
                    });

                    store.brands = brandsArray;
                    console.log("✅ Brands loaded from API:", store.brands);
                } catch (error) {
                    console.warn('⚠️ Failed to load brands, using mock fallback.', error);
                    store.brands = mockStore.brands;
                }

                app.render.brands();
            },

            loadPlates: async () => {
                try {
                    const platesResponse = await api.get('/api/number-plates');
                    if (platesResponse && platesResponse.status && Array.isArray(platesResponse['number_plates'])) {
                        store.plates = platesResponse['number_plates'].map(p => ({
                            id: p.id,
                            number: p.plate_number || 'N/A',
                            img: p.image ? '/storage/' + p.image : ''  // prepend storage path if needed
                        }));
                    } else {
                        throw new Error('Invalid API response for plates');
                    }

                    console.log("📌 Plates loaded:", store.plates);
                } catch (error) {
                    console.warn('⚠️ API failed. Using mock fallback for plates.');
                    store.plates = mockStore.plates; 
                }
                app.render.plates(); // Render after loading
            },

            loadTypes: async () => {
                try {
                    const typesResponse = await api.get('/api/vehicle_categories');
                    // 1. Check for success and access the 'vehicle_categories' array
                    let typesData = [];
                    if (typesResponse && typesResponse.success && Array.isArray(typesResponse.vehicle_categories)) {
                        typesData = typesResponse.vehicle_categories;
                    } else {
                        // Throw error if the expected structure is not found
                        throw new Error('API response did not contain the expected "vehicle_categories" array.');
                    }
                    
                    // 2. Map the data, providing a fallback icon
                    if (Array.isArray(typesData)) {
                        store.types = typesData.map(t => ({
                            id: t.id,
                            name: t.name,
                            // *** IMPORTANT CHANGE: The API is missing 'icon', so we use a fallback ***
                            icon: t.icon || 'fa-car' // Fallback to a default icon (e.g., 'fa-car' or 'fa-circle')
                        }));
                        console.log('✅ Types data loaded successfully from external API.');
                        
                        // Re-render the UI immediately after successful load
                        app.render.types(); 
                        
                    } else {
                        throw new Error('Processed types data was not an array.');
                    }
                } catch (error) {
                    console.error('❌ Failed to load types from API:', error);
                    // Ensure 'store.types' is reset to an empty array on failure
                    store.types = []; 
                    app.render.types(); // Call render to clear the grid if it was trying to display old data
                }
            },

            loadAll: async () => {
                try {
                    // Attempt to fetch from Laravel Backend
                    // Note: You might need to adjust this API call to pass headers if it is also protected.
                    const data = await fetch('/api/init-data').then(res => res.json());
                    store = data;
                    console.log('Data loaded from API');
                } catch (error) {
                    // Fallback for Preview / Blob environment
                    console.warn('API Connection failed (Expected in Preview Mode). Using Mock Data.');
                    store = mockStore;
                }
            },

            loadUsers: async () => {
                try {
                    const response = await api.get('/api/users'); // fetch from your API

                    if(response && response.status === 'success' && Array.isArray(response.users)) {
                        store.users = response.users.map(u => ({
                            id: u.id,
                            name: u.name,
                            email: u.email,
                            phone: u.phone,
                            joined: u.created_at ? new Date(u.created_at).toLocaleDateString() : 'N/A',
                            profile_image: u.profile_image
                        }));
                    } else {
                        throw new Error('Invalid API response');
                    }

                    app.render.users(); // update table
                    console.log('✅ Users loaded:', store.users);

                } catch (error) {
                    console.warn('⚠️ Failed to load users, using mock fallback.', error);
                    store.users = mockStore.users;
                    app.render.users();
                }
            },
            loadVehicles: async () => {
                try {
                    const response = await api.get('/api/vehicles'); // GET all vehicles
                    if (Array.isArray(response)) {
                        store.vehicles = response.map(v => ({
                            id: v.id,
                            ownerId: v.user_id,
                            typeId: v.vehicle_type_id,
                            brandId: v.brand_id,
                            model: v.model_name,
                            plate: v.license_plate,
                            color: v.vehicle_color,
                            year: v.vehicle_year,
                            type: v.type?.name || 'Unknown'
                        }));
                        console.log('✅ Vehicles loaded:', store.vehicles);
                    } else {
                        throw new Error('Invalid vehicles API response');
                    }
                } catch (error) {
                    console.warn('⚠️ Failed to load vehicles, using mock fallback', error);
                    store.vehicles = mockStore.vehicles;
                }
                app.render.vehicles();
            },
            loadTickets: async () => {
                try {
                    const response = await api.get('/api/tickets');

                    if(response && response.status === 'success' && Array.isArray(response.tickets)) {
                        store.tickets = response.tickets.map(t => ({
                            id: t.id,
                            userId: t.user_id,
                            category: t.category,
                            desc: t.other_text || '',
                            status: t.status,
                            lat: t.lat || 0,      // If you have lat/lng in API, else 0
                            lng: t.lng || 0,
                            date: t.created_at,
                            media: t.media || []
                        }));
                        console.log('✅ Tickets loaded:', store.tickets);
                    } else {
                        throw new Error('Invalid tickets response');
                    }
                } catch (error) {
                    console.warn('⚠️ Failed to load tickets, using mock fallback.', error);
                    store.tickets = mockStore.tickets;
                }

                app.render.tickets();
                app.render.dashboard();
            },


        },

        navigate: (sectionId, element) => {
            document.querySelectorAll('.content-section').forEach(el => el.classList.add('d-none'));
            const targetSection = document.getElementById(sectionId + '-section');
            if(targetSection) targetSection.classList.remove('d-none');
            
            document.querySelectorAll('.nav-link').forEach(el => el.classList.remove('active'));
            if(element) element.classList.add('active');

            const titles = {
                'dashboard': 'Dashboard', 'tickets': 'Service Tickets', 'users': 'User Management',
                'vehicles': 'Vehicle Management', 'types': 'Vehicle Categories', 'brands': 'Brand & Model Master',
                'plates': 'Number Plate Repository', 'settings': 'System Settings'
            };
            const titleEl = document.getElementById('pageTitle');
            if(titleEl) titleEl.innerText = titles[sectionId];
            
            if(sectionId === 'brands') app.render.brands();
            if(sectionId === 'dashboard') app.render.dashboard();

            if(window.innerWidth < 992) {
                document.getElementById('sidebar')?.classList.remove('show');
            }
        },

        render: {
            dashboard: () => {
                // Using helper to avoid null errors
                dom.setText('stat-users', store.users.length);
                dom.setText('stat-vehicles', store.vehicles.length);
                dom.setText('stat-tickets', store.tickets.filter(t => t.status === 'Open').length);
                dom.setText('stat-brands', store.brands.length);
                dom.setText('ticketBadge', store.tickets.filter(t => t.status === 'Pending').length);

                const tbody = document.getElementById('dashboard-tickets-body');
                if(tbody) {
                    tbody.innerHTML = store.tickets.slice(0, 5).map(t => {
                        const u = store.users.find(user => user.id === t.userId) || {name: 'Unknown'};
                        const badge = t.status === 'Pending' ? 'bg-gradient-warning' : 'bg-gradient-success';
                        return `<tr>
                            <td><span class="text-xs fw-bold">#${t.id}</span></td>
                            <td><div class="d-flex align-items-center">
                                <img src="https://ui-avatars.com/api/?name=${u.name}&background=random" class="avatar me-2"><span class="text-sm">${u.name}</span></div></td>
                            <td><span class="text-xs">${t.category}</span></td>
                            <td><span class="badge ${badge}">${t.status}</span></td>
                            <td><span class="text-secondary text-xs">${new Date(t.date).toLocaleDateString()}</span></td>
                        </tr>`;
                    }).join('');
                }
            },
            
            tickets: () => {
                const tbody = document.getElementById('tickets-table-body');
                if(!tbody) return;

                tbody.innerHTML = store.tickets.map(t => {
                    const u = store.users.find(user => user.id === t.userId) || {name: 'Unknown', phone: 'N/A'};
                    return `<tr>
                        <td><span class="text-xs fw-bold">#${t.id}</span></td>
                        <td>
                            <div class="d-flex px-2 py-1">
                                <div>
                                    <img src="https://ui-avatars.com/api/?name=${u.name}&background=random" class="avatar me-3"></div>
                                <div class="d-flex flex-column justify-content-center">
                                    <h6 class="mb-0 text-sm">${u.name}</h6>
                                    <p class="text-xs text-secondary mb-0">${u.phone}</p>
                                </div>
                            </div>
                        </td>
                        <td>
                            <p class="text-xs fw-bold mb-0">${t.category}</p>
                            <p class="text-xs text-secondary mb-0 text-truncate" style="max-width:150px">${t.desc}</p>
                        </td>
                        <td class="align-middle text-center"><span class="badge ${t.status === 'Pending' ? 'bg-warning' : 'bg-success'}">${t.status}</span></td>
                        <td>
                            <button class="btn btn-link text-secondary mb-0" onclick="app.modals.openTicketModal(${t.id})">
                                <i class="fa-solid fa-eye text-xs"></i>
                            </button>

                            <button class="btn btn-link text-danger mb-0" onclick="app.crud.delete('tickets', ${t.id})">
                                <i class="fa-solid fa-trash text-xs"></i>
                            </button>
                        </td>
                    </tr>`;
                }).join('');
            },

            users: () => {
                const tbody = document.getElementById('users-table-body');
                if(!tbody) return;

                tbody.innerHTML = store.users.map(u => `
                    <tr>
                        <td>
                            <div class="d-flex px-2 py-1">
                                <div><img src="https://ui-avatars.com/api/?name=${u.name}&background=random" class="avatar me-3"></div>
                                <div class="d-flex flex-column justify-content-center">
                                    <h6 class="mb-0 text-sm">${u.name}</h6>
                                    <p class="text-xs text-secondary mb-0">${u.email}</p>
                                </div>
                            </div>
                        </td>
                        <td><p class="text-xs fw-bold mb-0">${u.phone}</p></td>
                        <td><span class="text-secondary text-xs fw-bold">${u.joined}</span></td>
                        <td>
                            <button class="btn btn-link text-danger mb-0" onclick="app.crud.delete('users', ${u.id})">
                                <i class="fa-solid fa-trash text-xs"></i>
                            </button>
                        </td>
                    </tr>
                `).join('');
            },

            types: () => {
                const grid = document.getElementById('types-grid');
                if(!grid) return;

                grid.innerHTML = store.types.map(t => `
                    <div class="col-xl-3 col-md-4 col-6">
                        <div class="card card-body text-center shadow-sm border-0 h-100">
                            <i class="fa-solid ${t.icon} fs-2 text-primary mb-2"></i>
                            <h6 class="mb-1">${t.name}</h6>
                            <span class="text-xs text-muted">${store.vehicles.filter(v => v.typeId === t.id).length} Vehicles</span>
                            <div class="d-flex justify-content-center gap-3 mt-3">
                                <button class="btn btn-link text-danger p-0 mb-0" onclick="app.crud.delete('types', ${t.id})" title="Remove">
                                    <i class="fa-solid fa-trash text-sm"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                `).join('');
            },

            brands: () => {
                const grid = document.getElementById('brands-grid');
                if (!grid) return;

                grid.innerHTML = store.brands.map(b => {
                    const typeName = store.types.find(t => t.id == b.type_id)?.name || 'Unknown';
                    const modelsHtml = b.models.map(m => 
                        `<span class="badge rounded-pill bg-secondary bg-opacity-10 text-secondary border-0 fw-normal px-2 py-1 m-1">${m}</span>`
                    ).join('');

                    return `
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card h-100 border card-plain shadow-none position-relative">
                            <div class="card-body text-center p-4">
                                <div class="avatar avatar-xxl bg-white shadow-sm rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center border">
                                    <img src="${b.logo}" class="p-2" style="width: 100%; height: 100%; object-fit: contain;">
                                </div>
                                <h5 class="mb-1 text-dark">${b.name}</h5>
                                <span class="badge bg-light text-dark mb-3">${typeName}</span>
                                <hr class="horizontal dark my-3 opacity-2">
                                <div class="d-flex flex-wrap justify-content-center">
                                    ${modelsHtml}
                                </div>
                                <div class="position-absolute bottom-0 end-0 p-3 pt-3">
                                    <a href="javascript:;" onclick="app.crud.delete('brands', ${b.id})" class="text-danger" title="Delete">
                                        <i class="fa-solid fa-trash text-xs"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>`;
                }).join('');
            },

            vehicles: () => {
                const tbody = document.getElementById('vehicles-table-body');
                if(!tbody) return;

                tbody.innerHTML = store.vehicles.map(v => {
                    const owner = store.users.find(u => u.id === v.ownerId);
                    const brand = store.brands.find(b => b.id === v.brandId);
                    return `<tr>
                        <td>
                            <div class="d-flex flex-column">
                                <h6 class="mb-0 text-sm">${brand?.name} ${v.model}</h6>
                                <p class="text-xs text-secondary mb-0">ID: #${v.id}</p>
                            </div>
                        </td>
                        <td><p class="text-xs font-weight-bold mb-0">${owner?.name || 'Unknown'}</p></td>
                        <td class="align-middle text-sm"><div class="plate-card py-1 px-2" style="font-size:0.7rem">${v.plate}</div></td>
                        <td><span class="text-secondary text-xs fw-bold">${v.color} (${v.year})</span></td>
                        <td class="align-middle text-center"><span class="text-secondary text-xs font-weight-bold">${v.type}</span></td>
                        <td><button class="btn btn-link text-danger mb-0" onclick="app.crud.delete('vehicles', ${v.id})">
                            <i class="fa-solid fa-trash text-xs"></i>
                        </button> </td>             
                    </tr>`;
                }).join('');
            },

            plates: () => {
                const grid = document.getElementById('plates-grid');
                if(!grid) return;

                grid.innerHTML = store.plates.map(p => `
                    <div class="col-md-3 col-sm-6">
                        <div class="card card-body text-center border-0 shadow-sm">
                            <div class="plate-card mx-auto mb-2">${p.number}</div>
                            ${p.img ? `<img src="${p.img}" class="img-fluid rounded mb-2" style="height:40px; object-fit:contain;">` : ''}
                            <button class="btn btn-link text-danger btn-sm p-0" onclick="app.crud.delete('plates', ${p.id})">Remove</button>
                        </div>
                    </div>
                `).join('');
            }
        },

        modals: {
            openUserModal: () => {
                const el = document.getElementById('userModal');
                if(el) new bootstrap.Modal(el).show();
            },
            openTypeModal: () => {
                const el = document.getElementById('typeModal');
                if(el) new bootstrap.Modal(el).show();
            },
            openBrandModal: () => {
                const select = document.getElementById('brandTypeSelect');
                if(select) select.innerHTML = store.types.map(t => `<option value="${t.id}">${t.name}</option>`).join('');
                
                const container = document.getElementById('modelsContainer');
                if(container) container.innerHTML = '';
                
                app.crud.addModelInput();
                const el = document.getElementById('brandModal');
                if(el) new bootstrap.Modal(el).show();
            },
            openPlateModal: () => {
                const el = document.getElementById('plateModal');
                if(el) new bootstrap.Modal(el).show();
            },
            openVehicleModal: () => {
                const select = document.getElementById('bulkVehicleOwner');
                if(select) select.innerHTML = store.users.map(u => `<option value="${u.id}">${u.name}</option>`).join('');
                
                const tbody = document.querySelector('#bulkVehicleTable tbody');
                if(tbody) tbody.innerHTML = '';
                
                app.crud.addVehicleRow();
                const el = document.getElementById('vehicleModal');
                if(el) new bootstrap.Modal(el).show();
            },
            openTicketModal: (id) => {
    const ticket = store.tickets.find(t => t.id === id);
    if (!ticket) return;

    const user = store.users.find(u => u.id === ticket.userId) || {};

    // Set user details
    dom.setText('modalUserName', user.name || '');
    dom.setText('modalUserPhone', user.phone || '');
    dom.setText('modalCategory', ticket.category || '');
    dom.setText('modalDesc', ticket.desc || '');

    const img = document.getElementById('modalUserImg');
    if (img) img.src = `https://ui-avatars.com/api/?name=${user.name}`;

    const hiddenId = document.getElementById('modalTicketId');
    if (hiddenId) hiddenId.value = ticket.id;

    // 📌 MEDIA PREVIEW SECTION
    const mediaContainer = document.getElementById('modalMedia');
    if (mediaContainer) {
        mediaContainer.innerHTML = ""; // Clear old media

        if (ticket.media && ticket.media.length > 0) {
            ticket.media.forEach((file, i) => {
                if (file.type === "image") {
                    mediaContainer.innerHTML += `
                        <img src="${file.full_url}" 
                            style="width:70px;height:70px;object-fit:cover;border-radius:6px;cursor:pointer;"
                            onclick="app.modals.previewMedia('${file.full_url}', 'image')">
                    `;
                }

                if (file.type === "video") {
                    mediaContainer.innerHTML += `
                        <div style="width:70px;height:70px;position:relative;border-radius:6px;
                            background:#000;display:flex;align-items:center;justify-content:center;
                            cursor:pointer;overflow:hidden;"
                            onclick="app.modals.previewMedia('${file.full_url}', 'video')">
                            
                            <img src="https://img.icons8.com/ios-glyphs/60/ffffff/play-button-circled.png"
                                style="width:24px;height:24px;position:absolute;z-index:2;opacity:0.9;">
                            
                            <video style="width:100%;height:100%;opacity:0.3;object-fit:cover;">
                                <source src="${file.full_url}" type="video/mp4">
                            </video>
                        </div>
                    `;
                }
            });
        } else {
            mediaContainer.innerHTML = `<p class="text-xs text-muted">No media uploaded.</p>`;
        }
    }
    // 📌 END MEDIA SECTION

    // Show modal
    const el = document.getElementById('ticketModal');
    if (el) {
        new bootstrap.Modal(el).show();

        // Initialize map after modal shown
        setTimeout(() => {
            if (window.mapInstance) window.mapInstance.remove();
            const mapEl = document.getElementById('map');
            if (mapEl) {
                window.mapInstance = L.map('map').setView([ticket.lat, ticket.lng], 13);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(window.mapInstance);
                L.marker([ticket.lat, ticket.lng]).addTo(window.mapInstance);
            }
        }, 500);
    }
},

previewMedia: (url, type) => {
    let view = "";
    if (type === "image") {
        view = `<img src="${url}" style="width:100%;border-radius:10px;">`;
    } else if (type === "video") {
        view = `
            <video controls style="width:100%;border-radius:10px;">
                <source src="${url}" type="video/mp4">
            </video>`;
    }

    Swal.fire({
        html: view,
        showCloseButton: true,
        showConfirmButton: false,
        width: "60%"
    });
}


        },

        crud: {
    
            saveType: async () => {
                const name = document.getElementById('typeName')?.value;
                if(!name) return;
                
                // --- CONVERTED LOGIC: Prepare payload for the provided API ---
                const data = {
                    vehicle_categories: [name]
                };
                
                try { 
                    // Use the provided API endpoint
                    await api.post('/api/vehicle_category', data); 
                    await app.data.loadAll(); 
                    await app.data.loadTypes();
                } catch(e) { 
                    console.error('API call failed or failed to fetch all data:', e);
                    // Mock fallback
                    store.types.push({ name, id: Date.now(), icon: 'fa-circle' }); 
                }
                
                app.render.types();
                const el = document.getElementById('typeModal');
                if(el) bootstrap.Modal.getInstance(el).hide();
            },
            addModelInput: () => {
                const div = document.createElement('div');
                div.className = 'input-group input-group-sm';
                div.innerHTML = `
                    <input type="text" class="form-control model-input" placeholder="Model Name">
                    <button class="btn btn-outline-secondary" type="button" onclick="this.parentElement.remove()"><i class="fa-solid fa-times"></i></button>
                `;
                const container = document.getElementById('modelsContainer');
                if(container) container.appendChild(div);
            },
            
            savePlate: async () => {
                const plate_number = document.getElementById('plateNumber')?.value;
                if (!plate_number) return; // Correct

                const data = { plate_number };

                try {
                    await api.post('/api/number-plate', data);
                    await app.data.loadAll();
                    await app.data.loadPlates();
                } catch(e) {
                    console.error('API failure, adding locally');
                    store.plates.push({ id: Date.now(), number: plate_number });
                }

                app.render.plates();
                const el = document.getElementById('plateModal');
                if (el) bootstrap.Modal.getInstance(el).hide();

            },

            updateTicket: async () => {
                const id = document.getElementById('modalTicketId')?.value;
                if(!id) return;
                
                const data = {
                    id,
                    status: document.getElementById('modalStatusSelect')?.value
                };
                try { await api.post('/api/tickets', data); await app.data.loadAll(); } catch(e) { 
                    const t = store.tickets.find(tick => tick.id == data.id); if(t) t.status = data.status;
                }
                app.render.tickets(); app.render.dashboard();
                const el = document.getElementById('ticketModal');
                if(el) bootstrap.Modal.getInstance(el).hide();
            },
            
            // Bulk Vehicle Logic
            addVehicleRow: () => {
                const tbody = document.querySelector('#bulkVehicleTable tbody');
                if(!tbody) return;
                
                const tr = document.createElement('tr');
                const typeOpts = store.types.map(t => `<option value="${t.id}">${t.name}</option>`).join('');
                tr.innerHTML = `
                    <td><select class="form-select form-select-sm type-select" onchange="app.crud.handleTypeChange(this)">
                        <option value="">Select Type</option>${typeOpts}
                    </select></td>
                    <td><select class="form-select form-select-sm brand-select" disabled onchange="app.crud.handleBrandChange(this)"><option>Select Brand</option></select></td>
                    <td><select class="form-select form-select-sm model-select" disabled><option>Select Model</option></select></td>
                    <td><input type="text" class="form-control form-control-sm" placeholder="KA 01..."></td>
                    <td><input type="text" class="form-control form-control-sm" placeholder="Color"></td>
                    <td><input type="number" class="form-control form-control-sm" placeholder="2024" style="width:70px"></td>
                    <td class="text-center"><button class="btn btn-link text-danger p-0" onclick="this.closest('tr').remove()"><i class="fa-solid fa-trash"></i></button></td>
                `;
                tbody.appendChild(tr);
            },
            handleTypeChange: (select) => {
                const row = select.closest('tr');
                const brandSelect = row.querySelector('.brand-select');
                const typeId = select.value;
                if(!typeId) { brandSelect.disabled = true; return; }
                const filteredBrands = store.brands.filter(b => b.type_id == typeId);
                brandSelect.innerHTML = '<option value="">Select Brand</option>' + 
                    filteredBrands.map(b => `<option value="${b.id}">${b.name}</option>`).join('');
                brandSelect.disabled = false;
            },
            handleBrandChange: (select) => {
                const row = select.closest('tr');
                const modelSelect = row.querySelector('.model-select');
                const brandId = select.value;
                if(!brandId) { modelSelect.disabled = true; return; }
                const brand = store.brands.find(b => b.id == brandId);
                modelSelect.innerHTML = brand.models.map(m => `<option>${m}</option>`).join('');
                modelSelect.disabled = false;
            },
            
            // New function to handle the specific vehicle category deletion
            deleteType: async (id) => {
                const url = `/api/vehicle_category/${id}`;
                try {
                    console.log(`Attempting to delete vehicle category with ID: ${id} at ${url}`);
                    // Use the generic API delete helper
                    const response = await api.delete(url);

                    if (response && response.success) {
                        console.log(`✅ Vehicle category ${id} deleted successfully.`);
                    } else if (response && response.error) {
                        console.error(`❌ API reported error deleting category ${id}:`, response.error);
                    } else {
                        console.warn(`Category ${id} deleted but response was ambiguous. Reloading data.`);
                    }
                    
                    // After API call (success or ambiguous), reload all types and re-render the grid
                    await app.data.loadTypes();
                } catch (error) {
                    console.error(`❌ Failed to delete vehicle category ${id}:`, error);
                    // Fallback for mock store: remove locally
                    store.types = store.types.filter(t => t.id !== id);
                    app.render.types();
                }
            },
            // New/Updated generic delete function to route the call
            delete: (dataType, id) => {
                if (confirm(`Are you sure you want to delete this ${dataType.slice(0, -1)}?`)) {
                    switch (dataType) {
                        case 'users':
                            app.crud.genericDelete('/api/user/', id, app.render.users);
                            break;
                        case 'types':
                            // Route to the new deleteType function
                            app.crud.deleteType(id);
                            break;
                        case 'brands':
                            app.crud.genericDelete('/api/brands/', id, app.render.brands);
                            break;
                        case 'vehicles':
                            app.crud.genericDelete('/api/vehicle/', id, app.render.vehicles);
                            break;
                        case 'plates':
                            app.crud.genericDelete('/api/number-plate/', id, app.render.plates);
                            break;
                        case 'tickets':
                            app.crud.genericDelete('/api/tickets/', id, app.render.tickets);
                            break;
                        default:
                            console.error(`Unknown data type for deletion: ${dataType}`);
                    }
                }
            },
            
            // Generic function for simpler delete operations (if needed)
            genericDelete: async (baseUrl, id, renderCallback) => {
                const url = `${baseUrl}${id}`;
                try {
                    await api.delete(url);
                    await app.data.loadAll(); 

                    if(baseUrl.includes('number-plate')) {
                        await app.data.loadPlates();
                    }
                    if(baseUrl.includes('/api/brands')) {
                        await app.data.loadBrands();
                    }
                    if(baseUrl.includes('/api/vehicle')) {
                        await app.data.loadVehicles();
                    }
                    if(baseUrl.includes('/api/user')) {
                        await app.data.loadUsers();
                    }
                    if(baseUrl.includes('/api/tickets')) {
                        await app.data.loadTickets();
                    }
                } catch(e) { 
                    console.warn(`Delete failed for ${url} (Mocking removal)`);
                    // Simple mock removal - NOTE: The full `loadAll` handles this better, but this is a local fallback
                    // For a proper fallback, you'd need to filter the specific store array.
                }
                renderCallback();
                app.render.dashboard();
            },

            brandIndex: 0,

        addBrandInput: function() {
    const brandIndex = this.brandIndex++;
    const container = document.getElementById('brandsContainer');

    const brandDiv = document.createElement('div');
    brandDiv.classList.add('brand-item', 'border', 'p-3', 'rounded');
    brandDiv.dataset.index = brandIndex;

    brandDiv.innerHTML = `
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h6>Brand ${brandIndex + 1}</h6>
            <button type="button" class="btn btn-sm btn-danger" onclick="app.crud.removeBrandInput(this)">Remove Brand</button>
        </div>
        <div class="row g-2 mb-2">
            <div class="col-md-5">
                <input type="text" class="form-control brand-name" placeholder="Brand Name" required>
            </div>
            <div class="col-md-3">
                <select class="form-select brand-type" required>
                    <option value="">Select Vehicle Type</option>
                </select>
            </div>
            <div class="col-md-4">
                <input type="file" class="form-control brand-logo">
            </div>
        </div>
        <div class="submodels-container d-flex flex-column gap-2">
            <!-- Submodel inputs -->
        </div>
        <button type="button" class="btn btn-sm btn-outline-success mt-2" onclick="app.crud.addSubmodelInput(${brandIndex}, this)">+ Add Submodel</button>
    `;

    container.appendChild(brandDiv);

    // ✅ Populate vehicle types dynamically from store.types
    const select = brandDiv.querySelector('.brand-type');
    if (Array.isArray(store.types) && store.types.length > 0) {
        select.innerHTML = store.types
            .map(t => `<option value="${t.id}">${t.name}</option>`)
            .join('');
    } else {
        // Optional: fallback if types are not loaded yet
        select.innerHTML = `<option value="">No types available</option>`;
    }
},


        removeBrandInput: function(button) {
            button.closest('.brand-item').remove();
        },

        addSubmodelInput: function(brandIndex, button) {
            const brandDiv = button.closest('.brand-item');
            const submodelsContainer = brandDiv.querySelector('.submodels-container');
            const subIndex = submodelsContainer.children.length;

            const subDiv = document.createElement('div');
            subDiv.classList.add('row', 'g-2', 'align-items-center');
            subDiv.innerHTML = `
                <div class="col-md-5">
                    <input type="text" class="form-control submodel-name" placeholder="Submodel Name" required>
                </div>
                <div class="col-md-5">
                    <input type="file" class="form-control submodel-image">
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-danger btn-sm" onclick="this.closest('.row').remove()">Remove</button>
                </div>
            `;
            submodelsContainer.appendChild(subDiv);
        },

        saveBrands: async () => {
                const container = document.getElementById('brandsContainer');
                const brandItems = container.querySelectorAll('.brand-item');
                if (!brandItems.length) return;

                const formData = new FormData();

                brandItems.forEach((brandDiv, i) => {
                    const brandName = brandDiv.querySelector('.brand-name')?.value;
                    const vehicleType = Number(brandDiv.querySelector('.brand-type')?.value);
                    const logoFile = brandDiv.querySelector('.brand-logo')?.files[0];

                    if (!brandName || !vehicleType) return;

                    formData.append(`${i}[name]`, brandName);
                    formData.append(`${i}[vehicle_type_id]`, vehicleType);
                    if (logoFile) formData.append(`${i}[logo]`, logoFile);

                    const submodels = brandDiv.querySelectorAll('.submodels-container .row');
                    submodels.forEach((subDiv, j) => {
                        const subName = subDiv.querySelector('.submodel-name')?.value;
                        const subImage = subDiv.querySelector('.submodel-image')?.files[0];

                        if (!subName) return;

                        formData.append(`${i}[submodels][${j}][submodel_name]`, subName);
                        if (subImage) {
                            formData.append(`${i}[submodels][${j}][submodel_image]`, subImage);
                        }
                    });
                });
            console.log(formData);
                try {
                    await api.post('/api/vehicle_brands', formData);
                    await app.data.loadAll();
                    await app.data.loadBrands(); // ✅ make sure this updates store.brands
                } catch (e) {
                    console.error('API failure, adding brands locally', e);

                    brandItems.forEach((brandDiv, i) => {
                        const brandName = brandDiv.querySelector('.brand-name')?.value;
                        const vehicleType = Number(brandDiv.querySelector('.brand-type')?.value);
                        if (!brandName || !vehicleType) return;

                        const brandObj = {
                            id: Date.now() + i,
                            name: brandName,
                            type_id: vehicleType,
                            logo: '', 
                            models: Array.from(brandDiv.querySelectorAll('.submodels-container .submodel-name'))
                                        .map(input => input.value)
                        };

                        store.brands.push(brandObj);
                    });
                }

                app.render.brands(); // ✅ update UI

                const el = document.getElementById('brandModal');
                if (el) {
                    const modalInstance = bootstrap.Modal.getInstance(el) || new bootstrap.Modal(el);
                    modalInstance.hide();
                }
        },

        saveUser: async () => {
            const id = document.getElementById('userId')?.value;
            const name = document.getElementById('userName')?.value;
            const email = document.getElementById('userEmail')?.value;
            const phone = document.getElementById('userPhone')?.value;
            const password = document.getElementById('userPassword')?.value;
            const profileImage = document.getElementById('userProfileImage')?.files[0];

            if (!name || !email || !phone || !password) {
                return alert('Please fill all required fields.');
            }

                    const formData = new FormData();
                    formData.append('name', name);
                    formData.append('email', email);
                    formData.append('phone', phone);
                    formData.append('password', password);
                    formData.append('password_confirmation', password);
                    if (profileImage) formData.append('profile_image', profileImage);

                    try {
                        // Save user via API
                        const res = await api.post('/api/register', formData);

                        // Reload all users from API or fallback
                        await app.data.loadUsers();

                    // Optional: If you want, you can push the new user to store immediately
                    store.users.push({
                        id: res.id || Date.now(),
                        name, email, phone,
                        joined: new Date().toLocaleDateString(),
                        profile_image: profileImage ? URL.createObjectURL(profileImage) : null
                    });

                } catch (e) {
                    console.error('API failed, saving mock user locally', e);

                    store.users.push({
                        id: Date.now(),
                        name, email, phone,
                        joined: new Date().toLocaleDateString(),
                        profile_image: profileImage ? URL.createObjectURL(profileImage) : null
                    });
                }

                // Re-render user table
                app.render.users();

                // Clear the form fields
                const form = document.getElementById('userForm'); // Make sure your <form> has id="userForm"
                if (form) form.reset();

                // Hide the modal
                const el = document.getElementById('userModal');
                if (el) bootstrap.Modal.getInstance(el)?.hide();
            },
        saveBulkVehicles: async () => {
                const ownerId = document.getElementById('bulkVehicleOwner')?.value;
                const rows = document.querySelectorAll('#bulkVehicleTable tbody tr');
                const batch = [];

                rows.forEach(tr => {
                    const typeSelect = tr.querySelector('.type-select');
                    const brandSelect = tr.querySelector('.brand-select');
                    const modelSelect = tr.querySelector('.model-select');
                    const inputs = tr.querySelectorAll('input');

                    const vehicle_type = typeSelect?.selectedOptions[0]?.text || '';
                    const brand_name = brandSelect?.selectedOptions[0]?.text || '';
                    const model_name = modelSelect?.value || '';
                    const license_plate = inputs[0]?.value || '';
                    const color = inputs[1]?.value || '';
                    const year = Number(inputs[2]?.value) || null;

                    if(vehicle_type && brand_name && license_plate) {
                        batch.push({ vehicle_type, brand_name, model_name, license_plate, color, year });
                    }
                });

                if(batch.length > 0) {
                    try {
                        await api.post('/api/vehicles', batch); // Send array to API
                        await app.data.loadAll();
                        await app.data.loadTypes();
                        await app.data.loadBrands();
                        await app.data.loadVehicles(); 
                    } catch(e) {
                        console.error('API failed, saving locally', e);
                        batch.forEach(v => store.vehicles.push({ ...v, id: Math.floor(Math.random() * 10000) }));
                    }

                    app.render.vehicles();
                    app.render.dashboard();

                    const el = document.getElementById('vehicleModal');
                    if(el) bootstrap.Modal.getInstance(el)?.hide();
                }
            }



        }
    };

    // Initialize App
    document.addEventListener('DOMContentLoaded', app.init);

</script>