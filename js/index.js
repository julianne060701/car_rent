
        import { initializeApp } from "https://www.gstatic.com/firebasejs/11.6.1/firebase-app.js";
        import { getAuth, signInAnonymously, signInWithCustomToken, onAuthStateChanged } from "https://www.gstatic.com/firebasejs/11.6.1/firebase-auth.js";
        import { getFirestore, doc, getDoc, setDoc } from "https://www.gstatic.com/firebasejs/11.6.1/firebase-firestore.js";

        // Firebase configuration from the Canvas environment
        const appId = typeof __app_id !== 'undefined' ? __app_id : 'default-app-id';
        const firebaseConfig = typeof __firebase_config !== 'undefined' ? JSON.parse(__firebase_config) : {};
        const initialAuthToken = typeof __initial_auth_token !== 'undefined' ? __initial_auth_token : null;

        let db;
        let auth;
        let userId;

        // Initialize Firebase and set up authentication
        async function initializeFirebase() {
            try {
                const app = initializeApp(firebaseConfig);
                db = getFirestore(app);
                auth = getAuth(app);
                
                // Sign in the user using the custom auth token or anonymously
                if (initialAuthToken) {
                    await signInWithCustomToken(auth, initialAuthToken);
                } else {
                    await signInAnonymously(auth);
                }

                onAuthStateChanged(auth, async (user) => {
                    if (user) {
                        userId = user.uid;
                        console.log("Firebase authenticated. User ID:", userId);
                        // Load saved data for the authenticated user
                        await loadBookingData(userId);
                    } else {
                        console.log("No user is authenticated.");
                    }
                });

            } catch (error) {
                console.error("Error initializing Firebase:", error);
            }
        }

        // Show a custom message box instead of using alert()
        function showMessage(message, type = 'info') {
            const messageModal = document.getElementById('message-modal');
            const messageContent = document.getElementById('message-content');
            messageContent.innerHTML = `<p>${message}</p>`;
            if (type === 'success') {
                messageContent.classList.add('text-green-600');
            } else if (type === 'error') {
                messageContent.classList.add('text-red-600');
            } else {
                messageContent.classList.add('text-gray-800');
            }
            messageModal.classList.remove('hidden');
            messageModal.style.display = 'flex';
        }

        function closeMessageModal() {
            document.getElementById('message-modal').style.display = 'none';
        }

        // Save form data to Firestore
        window.saveBooking = async function() {
            if (!db || !userId) {
                showMessage("Authentication not ready. Please try again.", "error");
                return;
            }

            try {
                const form = document.getElementById('quick-booking-form');
                const formData = new FormData(form);
                const data = {};
                for (let [key, value] of formData.entries()) {
                    data[key] = value;
                }

                const docRef = doc(db, 'artifacts', appId, 'users', userId, 'quick_booking_data', 'saved_data');
                await setDoc(docRef, data, { merge: true });

                showMessage("Your booking progress has been saved!", "success");
                console.log("Document successfully written with ID:", docRef.id);

            } catch (e) {
                console.error("Error saving document:", e);
                showMessage("Error saving your data. Please try again.", "error");
            }
        };

        // Load form data from Firestore
        async function loadBookingData(currentUserId) {
            if (!db || !currentUserId) {
                console.log("Firebase not initialized or user ID not available. Cannot load data.");
                return;
            }

            try {
                const docRef = doc(db, 'artifacts', appId, 'users', currentUserId, 'quick_booking_data', 'saved_data');
                const docSnap = await getDoc(docRef);

                if (docSnap.exists()) {
                    const data = docSnap.data();
                    console.log("Saved data found:", data);

                    const form = document.getElementById('quick-booking-form');
                    for (const [key, value] of Object.entries(data)) {
                        const element = form.elements[key];
                        if (element) {
                            element.value = value;
                        }
                    }
                    showMessage("Your previous booking data has been loaded!", "info");
                } else {
                    console.log("No saved booking data found for this user.");
                }

            } catch (e) {
                console.error("Error loading document:", e);
            }
        }

        // --- Original JS functions, adapted for the new structure ---
        window.openBookingModal = function() {
            document.getElementById('booking-modal').style.display = 'flex';
        };

        window.openBookingModalWithVehicle = function(vehicleName) {
            document.getElementById('selected-vehicle').value = vehicleName;
            openBookingModal();
        };

        window.closeBookingModal = function() {
            document.getElementById('booking-modal').style.display = 'none';
        };

        // Initialize on window load
        window.onload = function() {
            initializeFirebase();
        };

