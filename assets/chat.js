// selecteurs
let chatDiv = document.querySelector('.overflow-auto');
let form = document.getElementById('form');
let channelId = form.querySelector("input[name=channel-id]").value;
let submit = document.querySelector('button');
let url = JSON.parse(document.getElementById("mercure-url").textContent);
let eventSource = new EventSource(url, {
    withCredentials: true
});
let messageInput = document.getElementById('message');

// listeners
window.onload = init;
form.addEventListener('submit', onFormSubmit);
submit.addEventListener('click', onSubmitClick);
eventSource.onmessage = onReceiveNewMessage;

// fonctions
function init() {
     // On souhaite scroller toujours jusqu'au dernier message du chat
    chatDiv.scrollTop = chatDiv.scrollHeight;
}

function onFormSubmit(event) {
    event.preventDefault(); // Empêche la page de se rafraîchir après le submit du formulaire
}

function onSubmitClick(e) { // On change le comportement du submit
    let message = document.getElementById('message'); // Récupération du message dans l'input correspondant
    let data = { // La variable data sera envoyée au controller
        'content': message.value, // On transmet le message...
        'channel': channelId // ... Et le canal correspondant
    }
    //console.log(data); // Pour vérifier vos informations
    fetch('/message', { // On envoie avec un post nos datas sur le endpoint /message de notre application
        method: 'POST',
        body: JSON.stringify(data) // On envoie les data sous format JSON
    }).then((response) => {
        messageInput.value = '';
        //console.log(response);
    });
}

function onReceiveNewMessage({data}) { // On écoute les événements publiés par le Hub
    let message = JSON.parse(data); // Le contenu des événements est sous format JSON, il faut le parser
    document.querySelector('.bg-light').insertAdjacentHTML( // On injecte le nouveau message selon le HTML déjà présent plus haut dans notre fichier Twig
        'beforeend',
            appUser === message.author.id ?
            `<div class="row w-75 float-right">
            <b>${message.author.email}</b>
            <p class="alert alert-info w-100">${message.content}</p>
        </div>` :
            `<div class="row w-75 float-left">
            <b>${message.author.email}</b>
            <p class="alert alert-success w-100">${message.content}</p>
        </div>`
    )
    messageInput.value = "";
    chatDiv.scrollTop = chatDiv.scrollHeight; // On demande au navigateur de scroller le chat tout en bas pour bien apercevoir le dernier message apparu
}