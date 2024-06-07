# EVENTI RICORRENTI IN AGENDA PER <a href="https://www.dolibarr.org">DOLIBARR ERP CRM</a>

## Caratteristiche
Permette la generazione di eventi ricorrenti nell'Agenda

<!--
![Screenshot agendarecurevents](img/screenshot_agendarecurevents.png?raw=true "Eventi ricorrenti in Agenda"){imgmd}
-->

Altri moduli sono a disposizione su <a href="https://www.dolistore.com" target="_new">Dolistore.com</a>.



### Traduzioni

Le traduzioni possono essere definite manualmente modificando i file nella cartella *langs*. 

<!--
This module contains also a sample configuration for Transifex, under the hidden directory [.tx](.tx), so it is possible to manage translation using this service. 

For more informations, see the [translator's documentation](https://wiki.dolibarr.org/index.php/Translator_documentation).

There is a [Transifex project](https://transifex.com/projects/p/dolibarr-module-template) for this module.
-->


<!--

Install
-------

### From the ZIP file and GUI interface

- If you get the module in a zip file (like when downloading it from the market place [Dolistore](https://www.dolistore.com)), go into
menu ```Home - Setup - Modules - Deploy external module``` and upload the zip file.


Note: If this screen tell you there is no custom directory, check your setup is correct: 

- In your Dolibarr installation directory, edit the ```htdocs/conf/conf.php``` file and check that following lines are not commented:

    ```php
    //$dolibarr_main_url_root_alt ...
    //$dolibarr_main_document_root_alt ...
    ```

- Uncomment them if necessary (delete the leading ```//```) and assign a sensible value according to your Dolibarr installation

    For example :

    - UNIX:
        ```php
        $dolibarr_main_url_root_alt = '/custom';
        $dolibarr_main_document_root_alt = '/var/www/Dolibarr/htdocs/custom';
        ```

    - Windows:
        ```php
        $dolibarr_main_url_root_alt = '/custom';
        $dolibarr_main_document_root_alt = 'C:/My Web Sites/Dolibarr/htdocs/custom';
        ```
        
### From a GIT repository

- Clone the repository in ```$dolibarr_main_document_root_alt/agendarecurevents```

```sh
cd ....../custom
git clone git@github.com:gitlogin/agendarecurevents.git agendarecurevents
```

### <a name="final_steps"></a>Final steps

From your browser:

  - Log into Dolibarr as a super-administrator
  - Go to "Setup" -> "Modules"
  - You should now be able to find and enable the module



-->


Licenze
--------

### Codice sorgente

![GPLv3 logo](img/gplv3.png)

GPLv3 o (a tua scelta) ogni versione successiva.

Consulta il file COPYING per maggiori informazioni.

#### Documentazione

Tutti i testi e i readme.

![GFDL logo](img/gfdl.png)
