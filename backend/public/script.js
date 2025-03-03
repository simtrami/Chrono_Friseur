// Fonction pour créer ou mettre à jour la visualisation
function createTimeline(events) {
    // Sélectionne l'élément div avec l'id 'timeline'
    const timeline = d3.select("#timeline");

    // Supprime l'ancien SVG s'il existe
    timeline.select("svg").remove();

    // Définit les dimensions du canevas en fonction de la taille de la fenêtre
    const width = window.innerWidth - 20;
    const height = window.innerHeight - 20;

    // Définit l'intervalle de temps pour la frise chronologique d'arrivée sur la page
    const startDate = new Date("1020-01-01");
    const endDate = new Date("2030-12-31");

    // Ajoute un élément SVG au div 'timeline', il contiendra tous les éléments de la frise chronologique
    const svg = timeline.append("svg")
        .attr("width", width)
        .attr("height", height)
        .call(d3.zoom().on("zoom", zoomed));

    // Ajoute un groupe "graduation" pour contenir les éléments de la frise en elle même
    const graduation = svg.append("g")
        .attr("transform", `translate(40, ${height / 2})`);

    // Ajoute un groupe "eventsGroup" pour contenir les éléments de la frise en elle même
    const eventsGroup = svg.append("g")
        .attr("transform", `translate(40, ${height / 2})`);

    // Exemple d'ajout d'une ligne pour la frise chronologique
    graduation.append("line")
        .attr("x1", 0)
        .attr("x2", width - 80)
        .attr("y1", 0)
        .attr("y2", 0)
        .attr("stroke", "#000")
        .attr("stroke-width", 2);

    // Calcule l'échelle de temps
    const timeScale = d3.scaleTime()
        .domain([startDate, endDate])
        .range([0, width - 80]);

    

    // Fonction pour ajouter des graduations avec trois types de ticks
    function addTicks(scale) {
        const domain = scale.domain();
        const range = scale.range();
        const duration = domain[1] - domain[0];
        const rangeDuration = range[1] - range[0];
        const pixelsPerDay = rangeDuration / (duration / (24 * 60 * 60 * 1000));
        let majorTicks, minorTicks;
        if (pixelsPerDay < 0.0002) {
            // work in progress
            // Affichage dynamique pour les périodes au dessus de l'ordre du millénaire
            frequenceMajorTicks = Math.pow(10,Math.round(Math.log10(0.2/pixelsPerDay)));
            frequenceMinorTicks = frequenceMajorTicks / 10;
            majorTicks = d3.timeYear.every(frequenceMajorTicks);
            minorTicks = d3.timeYear.every(frequenceMinorTicks);
        } else if (pixelsPerDay < 0.0008) {
            // Afficher des graduations pour les 5 siècles
            majorTicks = d3.timeYear.every(500);
            minorTicks = d3.timeYear.every(100);
        } else if (pixelsPerDay < 0.002) {
            // Afficher des graduations pour les siècles
            majorTicks = d3.timeYear.every(100);
            minorTicks = d3.timeYear.every(50);
        } else if (pixelsPerDay < 0.009) {
            // Afficher des graduations pour les demis siècles
            majorTicks = d3.timeYear.every(50);
            minorTicks = d3.timeYear.every(10);
        } else if (pixelsPerDay < 0.2) {
            // Afficher des graduations pour les décennies
            majorTicks = d3.timeYear.every(10);
            minorTicks = d3.timeYear.every(1);
        } else if (pixelsPerDay < 3) {
            // Afficher des graduations pour les années
            majorTicks = d3.timeYear.every(1);
            minorTicks = d3.timeMonth.every(1);
        } else if (pixelsPerDay < 17) {
            // Afficher des graduations pour les mois
            majorTicks = d3.timeMonth.every(1);
            minorTicks = d3.timeMonday.every(1);
        } else if (pixelsPerDay < 100) {
            // Afficher des graduations pour les semaines
            majorTicks = d3.timeMonday.every(1);
            minorTicks = d3.timeDay.every(1);
        } else {
            // Afficher des graduations pour les jours
            majorTicks = d3.timeDay.every(1);
            minorTicks = d3.timeYear.every(100);
        }

        // Ajoute des graduations secondaires (grises sans étiquettes)
        graduation.selectAll("line.minor-tick")
            .data(minorTicks.range(...domain))
            .join(
                enter => enter.append("line")
                    .attr("class", "minor-tick")
                    .attr("x1", d => scale(d))
                    .attr("x2", d => scale(d))
                    .attr("y1", -5)
                    .attr("y2", 5)
                    .attr("stroke", "#ccc")
                    .attr("stroke-width", 1),
                update => update
                    .attr("x1", d => scale(d))
                    .attr("x2", d => scale(d)),
                exit => exit.remove()
            );

        // Ajoute des graduations principales (noires avec étiquettes)
        graduation.selectAll("line.major-tick")
            .data(majorTicks.range(...domain))
            .join(
                enter => enter.append("line")
                    .attr("class", "major-tick")
                    .attr("x1", d => scale(d))
                    .attr("x2", d => scale(d))
                    .attr("y1", -10)
                    .attr("y2", 10)
                    .attr("stroke", "#000")
                    .attr("stroke-width", 1),
                update => update
                    .attr("x1", d => scale(d))
                    .attr("x2", d => scale(d)),
                exit => exit.remove()
            );

        // Ajoute des étiquettes pour les graduations principales
        graduation.selectAll("text.major-label")
            .data(majorTicks.range(...domain))
            .join(
                enter => enter.append("text")
                    .attr("class", "major-label")
                    .attr("x", d => scale(d))
                    .attr("y", 20)
                    .attr("text-anchor", "middle")
                    .attr("font-size", "12px")
                    .text(d => formatDate(d, scale)),
                update => update
                    .attr("x", d => scale(d))
                    .text(d => formatDate(d, scale)),
                exit => exit.remove()
            );

        

    }

    // Ajoute des graduations avec trois types de ticks
    addTicks(timeScale);

    if (events) {
        // Ajoute des rectangles pour chaque événement
        eventsGroup.selectAll("rect.event")
            .data(events)
            .enter()
            .append("rect")
            .attr("class", "event")
            .attr("x", d => timeScale(d.date) - 25)
            .attr("y", -30)
            .attr("width", 50)
            .attr("height", 20)
            .attr("fill", "steelblue");

        // Ajoute des étiquettes de texte pour chaque événement
        eventsGroup.selectAll("text.event-label")
            .data(events)
            .enter()
            .append("text")
            .attr("class", "event-label")
            .attr("x", d => timeScale(d.date))
            .attr("y", -15)
            .attr("text-anchor", "middle")
            .attr("font-size", "12px")
            .text(d => d.name);
    }

    // Fonction de zoom
    function zoomed(event) {
        const newXScale = event.transform.rescaleX(timeScale);

        // Met à jour les graduations avec deux types de ticks
        addTicks(newXScale);

        // Met à jour les rectangles d'événements
        eventsGroup.selectAll("rect.event")
            .attr("x", d => newXScale(d.date) - 25);

        // Met à jour les étiquettes d'événements
        eventsGroup.selectAll("text.event-label")
            .attr("x", d => newXScale(d.date));
    }

    // Fonction pour formater les dates en fonction de l'échelle de temps
    function formatDate(date, scale) {
        const domain = scale.domain();
        const range = scale.range();
        const duration = domain[1] - domain[0];
        const rangeDuration = range[1] - range[0];
        const pixelsPerDay = rangeDuration / (duration / (24 * 60 * 60 * 1000));


        if (pixelsPerDay < 3) {
            return date.getFullYear();
        } else if (pixelsPerDay < 17) {
            return d3.timeFormat("%B %Y")(date);
        } else if (pixelsPerDay < 100) {
            return d3.timeFormat("%d %B %Y")(date);
        } else {
            return d3.timeFormat("%A %d %B %Y")(date);
        }
    }
}

// Définit la nomenclature des dates
const dateFormat = d3.timeFormatDefaultLocale({
    "dateTime": "%A %e %B %Y, %X",
    "date": "%d/%m/%Y",
    "time": "%H:%M:%S",
    "periods": ["AM", "PM"],
    "days": ["dimanche", "lundi", "mardi", "mercredi", "jeudi", "vendredi", "samedi"],
    "shortDays": ["dim.", "lun.", "mar.", "mer.", "jeu.", "ven.", "sam."],
    "months": ["janvier", "février", "mars", "avril", "mai", "juin", "juillet", "août", "septembre", "octobre", "novembre", "décembre"],
    "shortMonths": ["janv.", "févr.", "mars", "avr.", "mai", "juin", "juil.", "août", "sept.", "oct.", "nov.", "déc."]
});

// Charge les données à partir du fichier CSV des événements
// d3.csv("events.csv").then(events => {
//     // Convertit les dates en objets JavaScript Date
//     events.forEach(d => {
//         d.date = new Date(d.date);
//     });

//     // Crée la visualisation initiale avec les données chargées
//     createTimeline(events);
// }).catch(error => {
//     console.error("Erreur lors du chargement du fichier CSV des événements:", error);
// });

// Charge les données à partir du fichier CSV des événements
d3.json("/events").then(events => {
    // Convertit les dates en objets JavaScript Date
    events.forEach(d => {
        d.date = new Date(d.date);
    });

    // Crée la visualisation initiale avec les données chargées
    createTimeline(events);
}).catch(error => {
    console.error("Erreur lors du chargement du fichier CSV des événements:", error);
});

// Redimensionne le canevas lorsque la fenêtre est redimensionnée
window.addEventListener("resize", () => {
    createTimeline();
});
