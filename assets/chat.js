let chatDiv = document.querySelector('.overflow-auto');
chatDiv.scrollTop = chatDiv.scrollHeight; // On souhaite scroller toujours jusqu'au dernier message du chat

let form = document.getElementById('form');
form.addEventListener('submit', event => {
  event.preventDefault(); // Empêche la page de se rafraîchir après le submit du formulaire
});

let channelId = form.querySelector("input[name=channel-id]").value;

const submit = document.querySelector('button');
submit.onclick = e => { // On change le comportement du submit
    const message = document.getElementById('message'); // Récupération du message dans l'input correspondant
    const data = { // La variable data sera envoyée au controller
        'content': message.value, // On transmet le message...
        'channel': channelId // ... Et le canal correspondant
    }
    console.log(data); // Pour vérifier vos informations
    fetch('/message', { // On envoie avec un post nos datas sur le endpoint /message de notre application
        method: 'POST',
        body: JSON.stringify(data) // On envoie les data sous format JSON
    }).then((response) => {
        message.value = '';
        console.log(response);
    });
}