document.addEventListener("DOMContentLoaded", () => {
    const historyList = document.getElementById("history-list");

    // Retrieve history from local storage
    const history = JSON.parse(localStorage.getItem("weatherSearchHistory")) || [];

    // Display history
    history.forEach(item => {
        const listItem = document.createElement("li");
        listItem.textContent = `${item.city} (${item.date}) - Temp: ${item.temperature}°C, Wind: ${item.wind} KPH, Humidity: ${item.humidity}%`;
        historyList.appendChild(listItem);
    });

    if (history.length === 0) {
        const listItem = document.createElement("li");
        listItem.textContent = "No history available.";
        historyList.appendChild(listItem);
    }
});
