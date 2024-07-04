const cityInput = document.querySelector(".city-input");
const searchButton = document.querySelector(".search-btn");
const locationButton = document.querySelector(".location-btn");
const currentWeatherDiv = document.querySelector(".current-weather");
const weatherCardsDiv = document.querySelector(".weather-cards");
const verificationInput = document.querySelector(".verification-input");
const verificationCodeInput = document.querySelector(".verification-code");
const verifyButton = document.querySelector(".verify-btn");
const emailField = document.querySelector(".email-field");
const subscribeButton = document.querySelector(".subscribe-btn");
const unsubscribeButton = document.querySelector(".unsubscribe-btn");

const API_KEY = "8d35a236a852479396060912240207"; // Replace with your API key

const createWeatherCard = (cityName, weatherItem, index) => {
    if (index === 0) { // HTML for the main weather card
        return `<div class="details">
                    <h2>${cityName} (${weatherItem.date})</h2>
                    <h6>Temperature: ${weatherItem.day.avgtemp_c}°C</h6>
                    <h6>Wind: ${weatherItem.day.maxwind_kph} KPH</h6>
                    <h6>Humidity: ${weatherItem.day.avghumidity}%</h6>
                </div>
                <div class="icon">
                    <h6>${weatherItem.day.condition.text}</h6>
                </div>`;
    } else { // HTML for the forecast cards
        return `<li class="card">
                    <h3>(${weatherItem.date})</h3>
                    <h6>Temp: ${weatherItem.day.avgtemp_c}°C</h6>
                    <h6>Wind: ${weatherItem.day.maxwind_kph} KPH</h6>
                    <h6>Humidity: ${weatherItem.day.avghumidity}%</h6>
                </li>`;
    }
}

const saveSearchHistory = (city, date, temperature, wind, humidity) => {
    const history = JSON.parse(localStorage.getItem("weatherSearchHistory")) || [];
    history.push({ city, date, temperature, wind, humidity });
    localStorage.setItem("weatherSearchHistory", JSON.stringify(history));
}

const getWeatherDetails = (cityName) => {
    const dates = [];
    for (let i = 0; i < 4; i++) {
        const date = new Date();
        date.setDate(date.getDate() + i);
        dates.push(date.toISOString().split('T')[0]);
    }

    const WEATHER_API_URL = `https://api.weatherapi.com/v1/history.json?key=${API_KEY}&q=${cityName}&dt=${dates[0]}`;

    fetch(WEATHER_API_URL).then(response => response.json()).then(data => {
        if (!data || !data.forecast || !data.forecast.forecastday) {
            alert("Invalid weather data!");
            return;
        }

        console.log('Weather Data:', data); // Debugging

        const weatherData = dates.map((date, index) => {
            return {
                date: date,
                day: {
                    avgtemp_c: data.forecast.forecastday[0].day.avgtemp_c + (index * 1), // Example modification for prediction
                    maxwind_kph: data.forecast.forecastday[0].day.maxwind_kph,
                    avghumidity: data.forecast.forecastday[0].day.avghumidity,
                    condition: data.forecast.forecastday[0].day.condition
                }
            };
        });

        console.log('Processed Weather Data:', weatherData); // Debugging

        // Save to history
        saveSearchHistory(cityName, weatherData[0].date, weatherData[0].day.avgtemp_c, weatherData[0].day.maxwind_kph, weatherData[0].day.avghumidity);

        // Clear previous weather data
        cityInput.value = "";
        currentWeatherDiv.innerHTML = "";
        weatherCardsDiv.innerHTML = "";

        // Create weather cards and add them to the DOM
        weatherData.forEach((weatherItem, index) => {
            const html = createWeatherCard(cityName, weatherItem, index);
            if (index === 0) {
                currentWeatherDiv.insertAdjacentHTML("beforeend", html);
            } else {
                weatherCardsDiv.insertAdjacentHTML("beforeend", html);
            }
        });
    }).catch(error => {
        console.error("Error fetching weather data:", error);
        alert("Error fetching weather forecast!");
    });
}

const getCityCoordinates = () => {
    const cityName = cityInput.value.trim();
    if (cityName === "") return alert("Please enter a city name.");
    getWeatherDetails(cityName);
}

const getUserCoordinates = () => {
    navigator.geolocation.getCurrentPosition(
        position => {
            const { latitude, longitude } = position.coords; // Get user's location coordinates
            const API_URL = `https://api.weatherapi.com/v1/current.json?key=${API_KEY}&q=${latitude},${longitude}`;
            fetch(API_URL).then(response => response.json()).then(data => {
                if (!data || !data.location) {
                    alert("Unable to find city name from coordinates!");
                    return;
                }
                const { name } = data.location;
                getWeatherDetails(name);
            }).catch(error => {
                console.error("Error fetching city name from coordinates:", error);
                alert("Error fetching city name!");
            });
        },
        error => { // Handle location access denied by the user
            if (error.code === error.PERMISSION_DENIED) {
                alert("Location request denied. Please enable location access.");
            } else {
                alert("Location request error. Please enable location access.");
            }
        });
}

// Ensure functions are defined before using them
locationButton.addEventListener("click", getUserCoordinates);
searchButton.addEventListener("click", getCityCoordinates);
cityInput.addEventListener("keyup", e => e.key === "Enter" && getCityCoordinates());

// Function to send verification email
const sendVerificationEmail = () => {
    const email = emailField.value.trim();
    if (email === "") {
        alert("Please enter an email address.");
        return;
    }

    fetch('send-verification-email.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `email=${encodeURIComponent(email)}`
    })
    .then(response => response.text())
    .then(data => {
        document.body.insertAdjacentHTML('beforeend', data); // Insert the response HTML into the body
    })
    .catch(error => {
        console.error('Error sending verification email:', error);
        alert('Error sending verification email.');
    });
};

// Function to verify code
const verifyCode = () => {
    const email = emailField.value.trim();
    const verificationCode = verificationCodeInput.value.trim();
    if (email === "" || verificationCode === "") {
        alert("Please enter both email and verification code.");
        return;
    }

    fetch('verify-code.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `email=${encodeURIComponent(email)}&verification_code=${encodeURIComponent(verificationCode)}`
    })
    .then(response => response.json())
    .then(data => {
        alert(data.message); // Display the message from the response
    })
    .catch(error => {
        console.error('Error verifying code:', error);
        alert('Error verifying code.');
    });
};

// Attach the functions to the respective buttons
subscribeButton.addEventListener("click", sendVerificationEmail);
verifyButton.addEventListener("click", verifyCode);
