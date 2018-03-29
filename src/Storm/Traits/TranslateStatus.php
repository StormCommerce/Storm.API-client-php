<?php


namespace Storm\Traits;


use Storm\StormClient;

trait TranslateStatus
{
    protected $svStatus = [
        'Unknown' => 'Okänd',
        'Allocation' => 'Mottagen',
        'Confirmed' => 'Bekräftad',
        'Back order' => 'Restorder',
        'Delivered' => 'Levererad',
        'Invoiced' => 'Fakturerad',
        'Cancelled' => 'Cancellerad',
        'Credit control' => 'Kreditkontroll',
        'Partly delivered' => 'Dellevererad',
        'Acknowledged' => 'Noterad',
        'ReadyForPickup' => 'Redo för upphämtning',
        'PickedUp' => 'Upphämtad',
        'NotPickedUp' => 'Ej upphämtad',
    ];
    protected $enStatus = [
        'Unknown' => 'Received',
        'Allocation' => 'Received',
        'Confirmed' => 'Confirmed',
        'Back order' => 'Back order',
        'Delivered' => 'Delivered',
        'Invoiced' => 'Invoiced',
        'Cancelled' => 'Cancelled',
        'Credit control' => 'Credit control',
        'Partly delivered' => 'Partly delivered',
        'Acknowledged' => 'Acknowledged',
        'ReadyForPickup' => 'ReadyForPickup',
        'PickedUp' => 'PickedUp',
        'NotPickedUp' => 'NotPickedUp',
    ];

    public function translatedStatus()
    {
        $array = $this->enStatus;
        $code =  StormClient::self()->application()->GetApplication()->Countries->Default->Code;
        if($code == 'SE') {
            $array = $this->svStatus;
        }
        if(isset($array[$this->Status])) {
            return $array[$this->Status];
        }
        return '';
    }
}