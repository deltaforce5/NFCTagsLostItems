<!DOCTYPE html>
<head>
    <!-- source from https://www.majesticform.com/form-guides/html-email-form -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>contact form</title>
</head>

<body>

<link href="site.css" rel="stylesheet">

<div class="fcf-body">

    <div class="fcf-form-center">
    	<?php include './sql.php'; ?>
    </div>
    
    <div id="fcf-form">
    <br />
    <h2 class="fcf-h3">Contattami/Contact Me</h3>

    <table class="fcf-form-center">
    
    	<thead><tr><th>IT</th><th>EN</th></tr></thead>
    	<tbody>
    	<tr><td>
    	Ciao! Se sei arrivato a questa pagina hai trovato un oggetto a cui è associato uno dei miei tag, per cortesia compila il modulo sottostante, in modo da poterci mettere in contatto e organizzare la restituzione. Grazie mille!
    	</td><td>
    	Hi! If you landed on this webpage you found an object with one of my tags attached to it, please fill the form below, in order to arrange the return of my item. Thank you very much!
    	</td></tr></tbody>
    </table>

    

    <br /><br />
    <form id="fcf-form-id" class="fcf-form-class" method="post" action="contact-form-process.php">
        
        <div class="fcf-form-group">
            <label for="Name" class="fcf-label">Il tuo nome/Your name</label>
            <div class="fcf-input-group">
                <input type="text" id="Name" name="Name" class="fcf-form-control" required>
            </div>
        </div>

        <div class="fcf-form-group">
            <label for="Name" class="fcf-label">Il tuo numero di telefono/Your phonenumber</label>
            <div class="fcf-input-group">
                <input type="text" id="Phoneno" name="Phoneno" class="fcf-form-control" value="(+39) " required>
            </div>
        </div>
        
        <div class="fcf-form-group">
            <label for="Email" class="fcf-label">Il tuo ndirizzo email/Your email address</label>
            <div class="fcf-input-group">
                <input type="email" id="Email" name="Email" class="fcf-form-control" required>
            </div>
        </div>

        <div class="fcf-form-group">
            <label for="Message" class="fcf-label">Messaggio/Message</label>
            <div class="fcf-input-group">
                <textarea id="Message" name="Message" class="fcf-form-control" rows="6" maxlength="3000" required>Ciao! ho trovato un tag, contattami al numero o all'indirizzo email sopra indicati.
                
Hi! I found your tag, please contact me at the recipients above.</textarea>
            </div>
        </div>

        <div class="fcf-form-group">
            <label for="tagid" class="fcf-label" style="display: none;">Tag-id</label>
            <div class="fcf-input-group">
                <input type="hidden" id="tagid" name="tagid" style="display: hidden;" required>
            </div>
        </div>

        <div class="fcf-form-group">
            <button type="submit" id="fcf-button" class="fcf-btn fcf-btn-primary fcf-btn-lg fcf-btn-block">Send Message</button>
        </div>

    </form>
    </div>

</div>
  <script>
    // Get the parameter from the URL
    const urlParams = new URLSearchParams(window.location.search);
    const myParam = urlParams.get('t');

    // Display the parameter on the page
    const parameterEl = document.getElementById('tagid');
    parameterEl.value = myParam;
  </script>
</body>
</html>
