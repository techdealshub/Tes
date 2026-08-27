const key = "API_KEY"; 
const secret = "API_SECRET";

const apiURL = `https://livescore-api.com/api-client/scores/live.json?key=${key}&secret=${secret}`;
const url = "https://corsproxy.io/?" + encodeURIComponent(apiURL);

async function getScores() {
    const res = await fetch(url);
    const data = await res.json();

    const div = document.getElementById("scores");

    data.data.match.forEach(m => {
        const p = document.createElement("p");
        p.textContent = `${m.home_name} ${m.score} ${m.away_name}`;
        div.appendChild(p);
    });
}
getScores();