<?php
  $lang_admincphelp = array(
    1 => 'Configuration value name. Leave this field blank to delete it.',
    2 => 'Value of the configuration variable.',
    3 => 'Configuration value name. This field is locked.',
    4 => 'Administrator note for specific variable, does nothing, just helps you identify variable.',
    5 => 'Adds more variables. If variable/config name is left empty it will not be added.',
    6 => 'Ovo je stranica stila ukljucena u HEAD sekciju (ne treba se dodavati u kod)',//This is stylesheet that is included in website head section (no need to enter it in this code)
    7 => 'Konfiguracijske Varijable',//Configuration Variables
    8 => 'sve konfiguracijske varijable',//whole range of configuration variables
    9 => 'Ulogiran kod',//Logged in content
    10=> 'Ulogiran kod ovdje',//Logged in content here
    11=> 'Odlogiran kod',//Logged out code
    12=> 'Odlogiran kod ovdje',//Logged out code here
    13=> 'Korisnicki info (ako je ulogiran)',//User info (if logged in)
    14=> 'ispisuje korisnicko ime',//prints username
    15=> 'ispisuje korisnicki session id',//prints users session id
    16=> 'ispisuje GM level',//prints users premission level
    17=> 'ispisuje vrijeme zadnje akcije (ucitana stranica)',//prints users last active time (page loaded)
    18=> 'ispisuje korisni&#269;ke informacije (array)',//prints users  info (array)
    19=> 'Admin kod',//Admin code
    20=> 'Admin kod ovdje',//Admin content here
    21=> 'Port Provjera ili Server online/offline Status',//Port Check aka Server online/offline Status
    22=> 'Ucita sve Realme',//Loops all Realms
    23=> 'Posebni IP i Port',//Specific server IP and Port
    24=> 'Selektirajte kod i pritisnite TAB da se pravilno formatira',//Select the code and use TAB key to format it correctly
    25=> 'Mozete koristiti PHP kod u ovom stilu',//You can use PHP code in this template file
    26=> 'Budite oprezni kako editirate ovu stranicu jer mozete sve zeznuti.<br>Ova web stranica podrzava jednu bazu sa accountima i vise baza sa likovima (realm\'s). Baze s likovima mogu se nalaziti na razlicitim hostovima. Morate "Regenerirati stranicu" prije nego promjene uzmu maha',//Be careful when changing settings becouse it could mess up whole website.<br>This website supports one account database and multiple characters (realm\'s) databases. Realm databases can be located on different hosts. You need to recache website before configuration takes effect
    27=> 'Ovaj plugin je samo normalna datoteka koja ce biti uba&#269;ena u stranicu, može se koristiti HTML i PHP. Sve CMS varijable &#263;e raditi.<br />Za napraviti SQL upit koristite ovo',//This plugin is just normal file that will be included to website, you can use HTML and PHP. All CMS variables will work.<br />To make sql query use this
    28=> 'Dobrodošli na sekciju s pluginovima, pluginovi su samo php datoteke koje su ukljucene u stranicu, sve CMS funkcije i varijable ce raditi, mozete raditi SQL upite itd...',//Welcome to plugin\'s section of Admin Panel, plugin is just a php file that is included to some part of your website, all CMS functions and variables will work with them so you can do queryes, extract database data so on so forth.
    29=> 'Ova scripta &#263;e napraviti strukturu koda ovako',//Link script will produce structure like this
    30=> 'Gdje <font color=orange>{X}</font> reprezentira naslov u kojoj je link. Na ovaj na&#269;in možemo definirati poseban CSS stil za svaku grupu linkova.',//Where <font color=orange>{X}</font> represents group title link is in. This way you can define link CSS style to it. Second link is link without description field defined.
    31=> 'Moglo bi pro&#263;i nešto vremena dok se stranica ne u&#269;ita.',//It might take few moments for page to load
    32=> 'Odabrani jezik ne postoji.',//Selected language does not exists.
    33=> 'Ime jezika ne može biti prazno',//Language name can not be empty
    34=> 'Odabrani naziv jezika vec postoji.',//This language already exists.
    35=> 'Oznacite module koje zelite obnoviti/instalirati. Za deinstalaciju modula morati cete rucno izbrisati datoteke',//Check the modules you want to update/install. To uninstall module you have to delete files manually
  );


$lang_admincp = array(
  
  "Welcome to WWCMS v2"=>"Dobrodošli u WWCMS v2",
  "Admin Control Panel"=>"Administracijsko Su&#269;elje",
  "Are you sure you wish to continue?"=>"Jeste li sigurni da želite nastaviti?",
  "Note"=>"Info",
  "add more"=>"dodaj više",
  "Main Page"=>"Glavna Stranica",
  "Your browser does not support iframes."=>"Vaš pretraživa&#269; ne podržava iframe element.",
  "is not cached"=>"nije generiran",
  
  "CMS Version"=>"Verzija CMS-a",
  "last update"=>"zadnja izmjena",
  "m/d/y"=>"m/d/g",
  "Update now"=>"Obnovi sad",
  "Enabled"=>"Omogu&#269;eno",
  "Disabled on this web server - Automated CMS update will not be possible."=>"Nije omogu&#269;eno - Automatsko obnavljanje CMS-a nije mogu&#263;e.",
  "View PHP info"=>"Pogledaj informacije o PHP-u",
  "License"=>"Licenca",
  "PHP fsockopen() is disabled, update is not possible using this method."=>"PHP fsockopen() nije omogu&#269;en, obnavljanje nije mogu&#263;e.",
  
  "Action report"=>"Izvješ&#263;e",
  "Template is saved"=>"Stil je spremljen",
  "Template is reversed"=>"Stil je vra&#269;en",
  "New data inserted"=>"Novi podatci su spremljeni",
  "No changes made"=>"Promjene nisu spremljene",
  "Go Back"=>"Idi Nazad",
  "There is some template elements missing on current selected style, change to style 1."=>"Neki elementi ne postoje u selektiranom stilu, promijenite u stil 1.",
  "Template"=>"Stil",
  "Code"=>"Kod",
  "Stylesheet"=>"Stranica stila",
  "Undo last save"=>"Ponisti zadnju izmjenu",
  "Save template"=>"Izmjeni",
  "Save"=>"Spremi",
  "PHP Variables and Code"=>"PHP varijable i kod",
  "Help"=>"Pomo&#263;",
  "Content Wrapper Top"=>"Gornji omota&#269;",
  "Content Wrapper Bottom"=>"Donji omota&#269;",
  "Template content <strong>before</strong> included module"=>"Kod stila <strong>prije</strong> uba&#269;enog modula",
  "content"=>"sadržaj",
  "Included module here"=>"Uba&#269;eni modul ovdje",
  "Template content <strong>after</strong> included module"=>"Kod stila <strong>poslije</strong> uba&#269;enog modula",
  "Template Wrapper top"=>"Gornji omota&#269;",
  "Template Wrapper bottom"=>"Donji omota&#269;",
  "Plugin here"=>"Ovdje plugin(ovi)",
  "Body"=>"Tijelo",
  "Suggested for table independent content"=>"Za sadžaj neovisan o tablicama",
  "Suggested for table elements"=>"Za elemente tablice",
  "Your new styleid is"=>"Vas novi styleid je",
  "Locked"=>"Zakljucano",
  
  "Clean Editor"=>"&#268;isti Ure&#273;iva&#269;",
  "Javascript Editor"=>"Javascript Ure&#273;iva&#269;",
  "CSS proprety"=>"CSS svojstva",
  "CSS element"=>"CSS element",
  
  "This is existing plugin"=>"Ovo je postoje&#263;i plugin",
  "Get Plugins"=>"Uzmi nove Pluginove",
  "will overwritte this one"=>"prebrisati &#263;e ovaj",
  "You are about to create a new Plugin"=>"Napravite novi Plugin",
  "Plugin"=>"Plugin",
  "is deleted"=>"je obrisan",
  "is saved"=>"je spremljen",
  "Return to plugins"=>"Povratak na pluginove",
  "is not saved, there was some problems"=>"nije spremljen, pojavili su se problemi",
  "is moved"=>"je pomaknut",
  "Plugin Info"=>"Plugin Info",
  "Options"=>"Opcije",
  "Wrapped"=>"Omotano",
  "Not Wrapped"=>"Nije omotano",
  "Before template content"=>"Prije sadržaja",
  "After template content"=>"Poslije sadržaja",
  "Check"=>"Provjera",
  "Action"=>"Akcija",
  "Save to"=>"Spremi u",
  "Deactivate"=>"Deaktiviraj",
  "Move to"=>"Pomakni u",
  "Order"=>"Poredak",
  "numeric"=>"brojevno",
  "Create new"=>"Napravi novo",
  "Plugins located in this element"=>"Pluginovi smješteni u ovom elementu",
  "Attached to element"=>"Na elementu",
  "located"=>"smješten",
  "before"=>"prije",
  "after"=>"poslije",
  "not"=>"nije",
  "No plugins attached to"=>"Nema pluginova spojenih na",
  "element yet"=>"element još",
  "Deactivated Plugins (will not be used in website)"=>"Deaktivirani Pluginovi (nece se koristiti u stranici)",
  
  "and cached"=>"i generirani",
  "links not cached"=>"linkovi nisu generirani",
  "Group"=>"Grupa",
  "Code to print links in this group"=>"Kod za printanje linkova u ovoj grupi",
  "second argument is link seperator in HTML code"=>"drugi argument je razdvaja&#269; linkova u HTML kodu",
  "Title"=>"Naslov",
  "Viewable"=>"Vidljivo",
  "Description"=>"Opis",
  "Save and Cache"=>"Spremi i Generiraj",
  
  "Create new"=>"Napravi novi",
  "Saved files"=>"Spremljene datoteke",
  "File"=>"Datoteka",
  "Example line"=>"Primjer",
  "Incorrect"=>"Neto&#269;no",
  "Correct"=>"To&#269;no",
  "Translate it like this"=>"Prevedi ovako",
  "Copying files"=>"Kopiranje datoteka",
  "From"=>"Od",
  "to"=>"do",
  "Start Editing"=>"Po&#269;ni Preva&#273;ati",
  
  "Main"=>"Glavno",
  "Used 3rd party scripts"=>"Koristene dodatne skripte",
  
  "Recache Website"=>"Regeneriraj Stranicu",
  "Delete"=>"Obriši",
  "Move"=>"Pomakni",
  "Edit"=>"Edit",
  

  "All"=>"Svi",
  "Guests"=>"Gosti",
  "All logged in"=>"Svi ulogirani",
  "Only GM's and Admins"=>"Samo GM-ovi i Admini",
  "Only Admins"=>"Samo Administratori",
  
  "License and Version"=>"Licenca i Verzija",
  "Configuration Variables"=>"Konfiguracija Varijabli",
  "Template Editor"=>"Izmjena Stila",
  "CSS Editor"=>"Izmjena CSS",
  "Plugins"=>"Pluginovi",
  "Languages"=>"Jezici",
  "Announcements &amp; News"=>"Najave i Novosti",
  "User Manager"=>"Korisnici",
  "Stats &amp; Logs"=>"Status &amp; Logovi",
  "Maintenance"=>"Održavanje",
  "Vote Manager"=>"Glasanje",
  "Menu Manager"=>"Linkovi",
  "Credits"=>"Prava",

  "Shown"=>"Pokazano",
  "Hidden"=>"Sakriveno",
  "Announcement"=>"Upozorenje",
  "News"=>"Vijest",
  "Choose image to send"=>"Odaberite sliku i posaljite",
  "Img Folder"=>"Slike u",
  "Please, 'Check' if plugin exists first"=>"Molimo, prvo izvrsite provjeru",
  "Allowed"=>"Dozvoljeno",
  
  
  "Update CMS"=>"Obnovi CMS",
  "Update is available"=>"Obnova je spremna za skidanje",
  "File list in this update"=>"Datoteke za obnovu",
  "Start Update Now"=>"Zapocnite obnovu",
  "Update/Install Modules"=>"Obnovi/Instaliraj Module",
  "Module selection"=>"Odabir modula",
);