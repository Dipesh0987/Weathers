const apiUrl = "http://localhost/prototype003/connection.php?t=";


const searchBox = document.querySelector(".search input");
const searchBtn = document.querySelector(".search button");
const weatherIcon = document.querySelector(".weather-icon");


async function checkWeather(city){
    try{
        if (!city.trim()){
            alert("Please enter city name")
            return;
        }
        const response = await fetch(`${apiUrl}${encodeURIComponent(city)}`);
        if(response.status === 404){
            alert("Please enter a valid city")
            document.querySelector(".search input").value = "";
            return;
        } else if (response.status === 401){
            alert("Invalid API Key")
        } else if(response.status === 500){
            alert("Server Error")
        } 
        const data = await response.json();
        console.log(data);
        const {
            City_Name = "Unknown City",
            Temperature = "N/A",
            Humidity = "N/A",
            Wind_speed = "N/A",
            Wind_Direction = "N/A",
            Pressure = "N/A",
            date  = "N/A",
            Icon_Code = ""
        } = data[0];


        elements.name.innerText = City_Name
        elements.temp.innerText = `${Temperature}°C`
        elements.humidity.innerText = `${Humidity}%`
        elements.wind.speed.innerText = `${Wind_speed} km/h`
        elements.wind.deg.innerText = `${Wind_Direction}°`
        elements.pressure.innerText = `${Pressure}hPa`
        elements.date.innerText = date

        


        if (Icon_Code){
            weatherIcon.src = `https://openweathermap.org/img/wn/${Icon_Code}@2x.png`;
        }

        // Get current date and time
        const now = new Date();

        // Format the date
        const options = { year: 'numeric', month: 'long', day: 'numeric' };
        const localDate = now.toLocaleDateString('en-US', options);

        // Get the current day
        const days = ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"];
        const currentDay = days[now.getDay()];

        // Display in the HTML
        document.querySelector(".date").innerHTML = localDate;
        document.querySelector(".day").innerHTML = currentDay;

    } catch (error){
        alert("Please try again");
        console.error("Error fetching data", error)
    }
}
const elements = {
    name: document.querySelector(".city"),
    temp: document.querySelector(".temp"),
    humidity: document.querySelector(".humidity"),
    wind: {
        speed: document.querySelector(".wind"),
        deg: document.querySelector(".wind-direction")
    },
    pressure: document.querySelector(".pressure"),
    date: document.querySelector(".date")
};

// function handleResponseError(status) {
//     switch (status) {
//         case 404:
//             alert("Please enter a valid city.");
//             searchBox.value = "";
//             break;
//         case 401:
//             alert("Invalid API Key.");
//             break;
//         case 500:
//             alert("Server error. Please try again later.");
//             break;
//         default:
//             alert("An unexpected error occurred.");
//     }
// }       

searchBtn.addEventListener("click", ()=>{
    checkWeather(searchBox.value);
})


checkWeather("Guntersville");