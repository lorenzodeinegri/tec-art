<?php

require_once ('Repository/EventsRepository.php');
require_once ('Utilities/DateUtilities.php');
require_once ('Utilities/InputCheckUtilities.php');

class EventsController {
    private $events;

    private static function checkInput($title, $description, $begin_date, $end_date, $type, $manager) {
        $message = '';

        if (strlen($title) === 0) {
            $message .= '[Non è possibile inserire un titolo vuoto]';
        } elseif (strlen($title) < 2) {
            $message .= '[Non è possibile inserire un titolo più corto di 2 caratteri]';
        } elseif (strlen($title) > 64) {
            $message .= '[Non è possibile inserire un titolo più lungo di 64 caratteri]';
        } elseif (!preg_match('/^[A-zÀ-ú0-9\/\'`!.,\-:()\s]+$/', $title)) {
            $message .= '[Il titolo contiene caratteri non consentiti. Quelli possibili sono lettere, anche accentate, numeri, spazi e i seguenti caratteri speciali \' ` ! . , - : ()]';
        }

        if (strlen($description) === 0) {
            $message .= '[Non è possibile inserire una descrizione vuota]';
        } elseif (strlen($description) < 30) {
            $message .= '[Non è possibile inserire una descrizione più corta di 30 caratteri]';
        } elseif (strlen($description) > 65535) {
            $message .= '[Non è possibile inserire una descrizione più lunga di 65535 caratteri]';
        }

        $begin_date_flag = false;
        $end_date_flag = false;
        if (strlen($begin_date) === 0) {
            $message .= '[Non è possibile inserire la data di inizio evento vuota]';
        } else {
            $formatted_date = DateTime::createFromFormat('d-m-Y', $begin_date);
            if ($formatted_date === false) {
                $message .= '[Non è possibile inserire la data di inizio evento espressa in un formato diverso da "<abbr title="giorno">gg</abbr>-<abbr title="mese">mm</abbr>-<abbr title="anno">aaaa</abbr>"]';
            } else {
                $date_properties = explode('-', $begin_date);
                if (!checkdate($date_properties[1], $date_properties[0], $date_properties[2])) {
                    $message .= '[La data di inizio evento inserita non è valida]';
                } else {
                    $begin_date_flag = true;
                }
            }
        }

        if (strlen($end_date) === 0) {
            $message .= '[Non è possibile inserire la data di fine evento vuota]';
        } else {
            $formatted_date = DateTime::createFromFormat('d-m-Y', $end_date);
            if ($formatted_date === false) {
                $message .= '[Non è possibile inserire la data di fine evento espressa in un formato diverso da "<abbr title="giorno">gg</abbr>-<abbr title="mese">mm</abbr>-<abbr title="anno">aaaa</abbr>"]';
            } else {
                $date_properties = explode('-', $end_date);
                if (!checkdate($date_properties[1], $date_properties[0], $date_properties[2])) {
                    $message .= '[La data di fine evento inserita non è valida]';
                } else {
                    $end_date_flag = true;
                }
            }
        }

        if($begin_date_flag && $end_date_flag) {
            $inserted_begin_date = DateTime::createFromFormat('Y-m-d', DateUtilities::italianEnglishDate($begin_date));
            $lower_bound = DateTime::createFromFormat('Y-m-d', date('Y-m-d'));
            $inserted_end_date = DateTime::createFromFormat('Y-m-d', DateUtilities::italianEnglishDate($end_date));
            $upper_bound = DateTime::createFromFormat('Y-m-d', date('Y-m-d', strtotime('+3 years')));

            $duration_limit = DateTime::createFromFormat('Y-m-d', DateUtilities::italianEnglishDate($begin_date));
            $duration_limit->modify('+6 month');

            if ($inserted_begin_date < $lower_bound) {
                $message .= '[Non è possibile inserire una data di inizio evento precedente alla data odierna]';
            } elseif ($inserted_begin_date > $upper_bound) {
                $message .= '[Non è possibile inserire una data di inizio evento successiva a tre anni dalla data odierna]';
            } elseif ($inserted_begin_date > $inserted_end_date) {
                $message .= '[Non è possibile inserire una data di inizio evento successiva alla data di fine evento]';
            } elseif ($inserted_end_date > $duration_limit) {
                $message .= '[Non è possibile inserire un evento che abbia una durata superiore ai sei mesi]';
            }
        }

        if ($type !== 'Mostra' && $type !== 'Conferenza') {
            $message .= '[La tipologia dell\'evento deve essere Mostra o Conferenza]';
        }

        if (strlen($manager) === 0) {
            $message .= '[Non è possibile inserire un organizzatore vuoto]';
        } elseif (strlen($manager) < 2) {
            $message .= '[Non è possibile inserire un organizzatore più corto di 2 caratteri]';
        } elseif (strlen($manager) > 64) {
            $message .= '[Non è possibile inserire un organizzatore più lungo di 64 caratteri]';
        } elseif (!preg_match('/^[A-zÀ-ú0-9\/\'`.:(\-)\s]+$/', $manager)) {
            $message .= '[L\'organizzatore dell\'evento contiene caratteri non consentiti. Quelli possibili sono lettere, anche accentate, numeri, spazi e i seguenti caratteri speciali \' ` . : - ()]';
        }

        return $message;
    }

    public function __construct() {
        $this->events = new EventsRepository();
    }

    public function __destruct() {
        unset($this->events);
    }

    public function addEvent($title, $description, $begin_date, $end_date, $type, $manager, $user) {
        $title = InputCheckUtilities::prepareStringForChecks($title);
        $description = InputCheckUtilities::prepareStringForChecks($description);
        $begin_date = InputCheckUtilities::prepareStringForChecks($begin_date);
        $end_date = InputCheckUtilities::prepareStringForChecks($end_date);
        $type = InputCheckUtilities::prepareStringForChecks($type);
        $manager = InputCheckUtilities::prepareStringForChecks($manager);
        $user = InputCheckUtilities::prepareStringForChecks($user);

        $message = EventsController::checkInput($title, $description, $begin_date, $end_date, $type, $manager);
        if ($message === '') {
            if ($this->events->postEvent($title, $description, DateUtilities::italianEnglishDate($begin_date), DateUtilities::italianEnglishDate($end_date), $type, $manager, $user)) {
                $message = '<p class="success">L\'evento ' . InputCheckUtilities::prepareStringForDisplay($title) . ' è stato inserito correttamente</p>';
            } else {
                $message = '<p class="error">Non è stato possibile inserire l\'evento ' . InputCheckUtilities::prepareStringForDisplay($title) . ', se l\'errore persiste si prega di segnalarlo al supporto tecnico.</p>';
            }
        } else {
            $message = '<p><ul>' . $message;
            $message = str_replace('[', '<li class="error">', $message);
            $message = str_replace(']', '</li>', $message);
            $message .= '</ul></p>';
        }

        return $message;
    }

    public function getSearchedEventsCount($search) {
        $search = InputCheckUtilities::prepareStringForChecks($search);
        $result_set = $this->events->getSearchedEventsCount($search);
        $count = $result_set->fetch_assoc()['Totale'];
        $result_set->free();
        return $count;
    }

    public function getEventsCount() {
        $result_set = $this->events->getEventsCount();
        $count = $result_set->fetch_assoc()['Totale'];
        $result_set->free();
        return $count;
    }

    public function getEventsCountByType($type) {
        $type = InputCheckUtilities::prepareStringForChecks($type);
        $result_set = $this->events->getEventsCountByType($type);
        $count = $result_set->fetch_assoc()['Totale'];
        $result_set->free();
        return $count;
    }

    public function getSearchedEvents($search, $offset, $button) {
        $search = InputCheckUtilities::prepareStringForChecks($search);
        $result_set = $this->events->getSearchedEvents($search, $offset);

        $id = 'event';
        $counter = 1;
        $content = '';

        while($row = $result_set->fetch_assoc()) {
            $content .= '
                <dt id="' . $id . $counter . '" class="titleDef">
                     <a href="Evento.php?id=' . $row['ID'] . '\" aria-label="Vai all\'evento" title="' . InputCheckUtilities::prepareStringForDisplay($row['Titolo']) . '">' . InputCheckUtilities::prepareStringForDisplay($row['Titolo']) . '</a>
                </dt>
                <dd>
                    <a href="#' . ($result_set->num_rows === $counter ? $button : $id . ($counter + 1)) . '" class="skipInformation" title="Vai all\'evento successivo" aria-label="Vai all\'evento successivo">Vai all\'evento successivo</a>
    
                    <dl>
                        <dt class="inlineDef">
                            Data inizio evento: 
                        </dt>
                        <dd class="definition">
                            ' . DateUtilities::englishItalianDate($row['DataInizio']) . '
                        </dd>
                        
                        <dt class="inlineDef">
                            Data fine evento: 
                        </dt>
                        <dd class="definition">
                            ' . DateUtilities::englishItalianDate($row['DataFine']) . '
                        </dd>
    
                        <dt class="inlineDef">
                            Tipologia: 
                        </dt>
                        <dd class="definition">
                            ' . InputCheckUtilities::prepareStringForDisplay($row['Tipologia']) . '
                        </dd>
                    </dl>
                </dd>
            ';

            $counter++;
        }

        $result_set->free();

        return $content;
    }

    public function getEvents($type, $offset, $button) {
        $type = InputCheckUtilities::prepareStringForChecks($type);
        if($type === 'TuttiGliEventi') {
            $result_set = $this->events->getEvents($offset);
        } else {
            $result_set = $this->events->getEventsByType($type, $offset);
        }

        $id = 'event';
        $counter = 1;
        $content = '';

        while($row = $result_set->fetch_assoc()) {
            $content .= '
                <dt id="' . $id . $counter . '" class="titleDef">
                     <a href="Evento.php?id=' . $row['ID'] . '\" title="Vai all\'evento ' . InputCheckUtilities::prepareStringForDisplay($row['Titolo']) . '" aria-label="Vai all\'evento ' . InputCheckUtilities::prepareStringForDisplay($row['Titolo']) . '">' . InputCheckUtilities::prepareStringForDisplay($row['Titolo']) . '</a>
                </dt>
                <dd>
                    <a href="#' . ($result_set->num_rows === $counter ? $button : $id . ($counter + 1)) . '" class="skipInformation" title="Vai all\'evento successivo" aria-label="Vai all\'evento successivo">Vai all\'evento successivo</a>
    
                    <dl>
                        <dt class="inlineDef">
                            Data inizio evento: 
                        </dt>
                        <dd class="definition">
                            ' . DateUtilities::englishItalianDate($row['DataInizio']) . '
                        </dd>
                        
                        <dt class="inlineDef">
                            Data fine evento: 
                        </dt>
                        <dd class="definition">
                            ' . DateUtilities::englishItalianDate($row['DataFine']) . '
                        </dd>
    
                        <dt class="inlineDef">
                            Tipologia: 
                        </dt>
                        <dd class="definition">
                            ' . InputCheckUtilities::prepareStringForDisplay($row['Tipologia']) . '
                        </dd>
                    </dl>
                </dd>
            ';

            $counter++;
        }

        $result_set->free();

        return $content;
    }

    public function getEventsTitle($type, $offset, $quantity = 5) {
        $type = InputCheckUtilities::prepareStringForChecks($type);

        if($type === '') {
            $result_set = $this->events->getEventsOrderByTitle($offset, $quantity);
        } else {
            $result_set = $this->events->getEventsByTypeOrderByTitle($type, $offset, $quantity);
        }

        $content = '';

        while($row = $result_set->fetch_assoc()) {
            $content .= '
                <li>
                    <a href="Evento.php?id=' . $row['ID'] . '" title="Vai all\'evento ' . InputCheckUtilities::prepareStringForDisplay($row['Titolo']) . '" aria-label="Vai all\'evento ' . InputCheckUtilities::prepareStringForDisplay($row['Titolo']) . '" > ' . InputCheckUtilities::prepareStringForDisplay($row['Titolo']) . '</a>
                    
                    <form class="userButton" action="EliminaContenuto.php" method="post" role="form">
                        <fieldset class="hideRight">
                            <legend class="hideLegend">Pulsanti di modifica ed eliminazione dell\'evento</legend>
                            
                            <input type="hidden" name="id" value="' . $row['ID'] . '"/>
                            <input type="hidden" name="type" value="Evento"/>
                            
                            <a class="button" href="ModificaEvento.php?id=' . $row['ID'] . '" title="Modifica dettagli evento" role="button" aria-label="Modifica dettagli evento">Modifica</a>
                            <input class="button" name="submit" type="submit" value="Rimuovi" role="button" title="Rimuovi evento" aria-label="Rimuovi evento"/>
                        </fieldset>
                    </form>
                </li>
            ';
        }

        $result_set->free();
        return $content;
    }

    public function getEvent($id) {
        $result_set = $this->events->getEvent($id);
        $row = $result_set->fetch_assoc();
        $result_set->free();
        return $row;
    }

    public function updateEvent($id, $title, $description, $begin_date, $end_date, $type, $manager, $user) {
        $title = InputCheckUtilities::prepareStringForChecks($title);
        $description = InputCheckUtilities::prepareStringForChecks($description);
        $begin_date = InputCheckUtilities::prepareStringForChecks($begin_date);
        $end_date = InputCheckUtilities::prepareStringForChecks($end_date);
        $type = InputCheckUtilities::prepareStringForChecks($type);
        $manager = InputCheckUtilities::prepareStringForChecks($manager);
        $user = InputCheckUtilities::prepareStringForChecks($user);

        $message = EventsController::checkInput($title, $description, $begin_date, $end_date, $type, $manager);

        if ($message === '') {
            if ($this->events->updateEvent($id, $title, $description, DateUtilities::italianEnglishDate($begin_date), DateUtilities::italianEnglishDate($end_date), $type, $manager, $user)) {
                $message = '';
            } else {
                $message = '<p class="error">Non è stato possibile aggiornare l\'evento ' . InputCheckUtilities::prepareStringForDisplay($title) . ', se l\'errore persiste si prega di segnalarlo al supporto tecnico.</p>';
            }
        } else {
            $message = '<p><ul>' . $message;
            $message = str_replace('[', '<li class="error">', $message);
            $message = str_replace(']', '</li>', $message);
            $message .= '</ul></p>';
        }

        return $message;
    }

    public function deleteEvent($id) {
        return $this->events->deleteEvent($id);
    }
}

?>
