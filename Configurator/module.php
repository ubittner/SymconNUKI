<?php

/*
 * @module      NUKI Configurator
 *
 * @file        module.php
 *
 * @author      Ulrich Bittner
 * @copyright   (c) 2019
 * @license     CC BY-NC-SA 4.0
 *
 * @version     1.04
 * @build       1005
 * @date        2019-04-21, 10:00
 *
 * @see         https://github.com/ubittner/SymconNUKI
 *
 * @guids		Library
 * 				{752C865A-5290-4DBE-AC30-01C7B1C3312F}
 *
 *				Virtual I/O (Server Socket NUKI Callback)
 *				{018EF6B5-AB94-40C6-AA53-46943E824ACF} (CR:	IO_RX)
 *				{79827379-F36E-4ADA-8A95-5F8D1DC92FA9} (I: 	IO_TX)
 *
 *				Spliter (NUKI Bridge)
 *				{B41AE29B-39C1-4144-878F-94C0F7EEC725} (Module GUID)
 *
 * 				{79827379-F36E-4ADA-8A95-5F8D1DC92FA9} (PR:	IO_TX)
 *				{3DED8598-AA95-4EC4-BB5D-5226ECD8405C} (CR: Device_RX)
 * 				{018EF6B5-AB94-40C6-AA53-46943E824ACF} (I:	IO_RX)
 *				{73188E44-8BBA-4EBF-8BAD-40201B8866B9} (I:	Device_TX)
 *
 *				Device (NUKI Smartlock)
 *				{37C54A7E-53E0-4BE9-BE26-FB8C2C6A3D14} (Module GUID)
 *
 * 				{73188E44-8BBA-4EBF-8BAD-40201B8866B9} (PR: Device_TX)
 *				{3DED8598-AA95-4EC4-BB5D-5226ECD8405C} (I: 	Device_RX)
 *
 * @changelog	2019-04-21, 10:00, added changes for module store
 *              2018-04-21, 12:30, rebuild for IP-Symcon 5.0
 * 				2017-04-19, 23:00, update to API Version 1.5 and some improvements
 * 				2017-01-18, 13:00, initial module script version 1.01
 *
 */

// Declare
declare(strict_types=1);

class NUKIConfigurator extends IPSModule
{
    public function Create()
    {
        //Never delete this line!
        parent::Create();

        // Register properties
        $this->RegisterPropertyInteger('CategoryID', 0);

        // Connect to parent
        $this->ConnectParent('{B41AE29B-39C1-4144-878F-94C0F7EEC725}');
    }

    public function ApplyChanges()
    {
        //Never delete this line!
        parent::ApplyChanges();
    }

    public function GetConfigurationForm(): string
    {
        $form['elements'][] = [
            'type'  => 'Image',
            'image' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAJYAAABECAYAAABj98zGAAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAAAJcEhZcwAADsMAAA7DAcdvqGQAAAnaSURBVHhe7d0HqGRXHcfxuLGXaOwaEzsqdsliZzF2Y0WDilixawwaFRV7xxIbKopG7Ao27AXFta6uiQVbbIklauzGGk02/j7zdmQYb597Z55wvvDjvbm775Zz/+f8z/mf/zlzjgMKhY7s27trR35cNLp9dOfo8Oi80SnRx6O3+33Hzt1nFsMqdCJGxVYOiR4YPTK6VLRoP/+OPhS9PNrDAguFLhwU3SV6cHTpaLlROld01+jo6JBiWIWuXDm6d3To7FM17InxHVUMq9CVK0ZXiNq6T+eOZp2xQqELF48O3vq1lcOKYRW6cub+n104oxhWoSs/i3679Wsj+6ITi2EVunJSdELU1nL9JvpMMaxCV34RvSb6XnS2A0s45v+8MdpTAqSFzuzbu0us6qbRs6OrR2Jb+FvEVb4jel90cjGsQi9iXAfmB6MynSMEwdhOi74b7Y1O37Fz99nFsAqD2D9vSAyNGzwrBnVWfhYKhUKhUCgUCoVCoVAoFAqFQqFQKBQKhUKhUCj8HyBtRm6NFRh/jb4Rjc35oqZMVSto/7X16yDOGZ1n69daJKL1RdnINaIqpIqcEa2aKnL+qC59yTWUTZ+FDLjA/p91OJ9774sUGcu76t7nf9+lB/pydP3IhSyd/nD052gs7hZZ61/1sAruR9Fzo76FN2e+kPJqUdUDu8bx0Wcjif5duWBk8aV7r3rxDGpP9K7oDw4MgNE+JrpOpIIso0y+Fr1q9qkb9lZ4eKTBYAjLKA956c+K+rxnZWBdobK+pgMV/Cl6Z6RcZhdxMZJeyrjmKadj8IaIJc+vsazfR1rModwykofNaKrOT2+K2mrxMpeL3htVnW8uGZM3iIbCCE6O6u7d8S4rY+Z4b4+IlGlTeXgfN4/6oNLeOvp2VHVO+nv0wugg/3mxNlo+/bjoyNmncVDzq2rjHLuVcJdDce4mdwL/3nQPVSibNmPkgtvccBPu6WJR3b073rXSuQ/56PePLCxtKg/GevrWr71wjaZ35Zr+/cAq18G1vC3Scl0karrBwuqMVb43ip4cyUWvOyf3/Z1I4/FNB6aiyrDg+JOiB0RqVGF7c4nosdHOqKpfNed3kZU0n5p9mpA6wwK3+PiIcRW2L7oaD430NXUrmvhY9O7oj7NPE9JkWLDRls7YE6JLRsUtbi90/h8SPSpiYHXorH8h0qr9OOozOh5Em2FB03psZAjLuArbA5X8FtF9I7vr1aFfZSSngRAOWAtdDAtuXK04Jur6N4Vpmb+Ta0VN7+TnkWXvn5t9WhN9jERrZdTxkoiLLAa2GXgQxvTqSItVNzOAf0aCqwLEf3FgXQwxjvtFgnDFLW4GI8C7RzeZfaqHUdnJ+C3RPxxYJ0MMS4fxQZF+V9PQtjANt4nuEzVVbP0q876vjIQY1s4Qw9JpvEzEsJ4X2UG3MD1c3q7IHN+VoqZ39/XouMh+VhthlX4SAxOdf3pk15HCdPAM14uEC0wEN3kKIz/9L25wyLTNKKxiWBA7uUf01KgtOFcYDg/B/R0x+1SPvtQHovdHa+2sL8OwzEoPRaulM+nbCl4RNe0BXhiOTfulqzRNijMkUXXfDDFm2tMgGJZUh1VhYAJ1L4iu6kBhVMSrdNabPMxXordGUog2jhs1LG3Cbm1dcoKkS9wpYlwXcqAwGm2pMxIN7Q/6pWiVbNzR6NLH+mWkKf5+1OY2JZqJsUisk9GpJStMiyxT37r10aitkVgbXQwLRhjSh7s2s3eMnhbVpbAWxkOyoDBE1RcnbYyuhqV5/WBkt9wuHUOZhtziM6KSzzU9KrB53G0TU+xqWDDqeE8k+V/qRRe3aCHFjWefCqvQluoirmU2RO6cNOyN08ewYKrAqhRBUX2uNjxw30UMhf9Fa/STqKkyXzh6WHSvaOMusa9hgVv0TZqmcybPRCzM+GLUZZmZOOKjI9H5jTLEsDAPxulD/cqBDaIWt7ll82x9n1WnuG2Sve26Y2Ga5vXRJ6Oma3pGUz/PjC7rwKYYalgwzLVmkHFZdLopDLHbVvWaEqGucCVyzpoyCLxgUyjrGuL7nhoj7fk6xDrc+1GRecWNDZxWMSwoWIs6XxRZor8JTLS2jVQtPr1G1HVtoVGtlcRNxsiwXHddz+16jOqlkdhiE+ZtzeEamW+EVQ0L/P6bIznVm5ijkm9kdqDJRTAQy+XlknVBWorszKYV4RYoeMHrrFBaKsu3dEOakve0WpeP9IPN5a6dMQwLOvQmoTXVlumvE8bsC4KaXJK+0h0iC0KasjCUB/dh3vO2UdMqZ3OsP4zWnUXgeV8bfWT2qR7GpZ/1uqjLdzmPyliGBTVXbdJUD9ndZShqrlUoba2l1spkruVSh0XLHXNGJJfc0F5MqO37j+154brrnkbRMp8SyWLQUrch1UY6uVXta2U+B1inE6M+CNAZlRjJVJ1vWYxw1XQbruvTUdX5l6WFscbOPd4qulnETb442h1xrVV/tyzDf9ddBYMD91N1/kVVoWI8Mery96dG9nRYBY2Qqbp5cLxKKjnPNauUYxsWJACK0P80qjrnosYwLA99z4hLrrpGnfRZ/I3Ab9W/10n87nbRqu5lFcOCv5ctqgyr/m5RuiimfoZ6qV6GNaYrXIRbNOMumb+pkzkWDMTsvr2+mobiyzCMvjEuoY1PRFo9hblJpDPJJLGHVhsqr/041tLfmsqwYI8mQT15QmMkE7ah5lto0Db1sQoqyecjHeJNhVcW8ZwWTjw/0vK2IQRhn4cx9z+rZErDgpetL2O0yM9PjZdun4lvzT6Ni5eoZXhZpLXaLnDjWlAJlm0DGCNiuXUGMJO++6kNC2q2OJcXMvUISmzJtIeY2q+jsVou7lVf1IhXB991thuW0TOwtpZLOOXoyPaUk7nEdRgWBFEt87aCZMimqn3gduWOmQ3QYV3VuBiRLSGfEzHadYZS+mCPBhVYKKKN+WR102YiKyO4qPDrpFDHQmqHpWKL+56OMSqsQm203pGLMDplcFqexWerE/fCrdj97imRKPYUtVsuu+tU3cNcXfpOc9yjaRxeoupcixJWsVWCXZC7oBESZDYvXHU+0gcVXztYLOSGkRc+n3NblJbGLiVtUd6uaK1OihSAvQdcn3s0RJ2iMyyWZqQoQu5ZGIyFHoKhVYbi3xW4NBVr8ww+TJ9MtUydoUuENDKtKn9hja9GRthd0WKZxvFOVdqq89IPIq6ToXQdSQsjXTvSV6s6p0wXuwWeoHBFoeu2snZBtd1NjIVrioJrCa4baRUE+qZ0ka4paGeKw2S0CearROYQ1VgvwASvEaWfcvv10Qw+GNtUuC97vmpZ68rfyxLh74NnFbNqW4eo7Bme1qYLDIqt1KVAu9/0RQ849T8QO7J1rK2MIQAAAABJRU5ErkJggg=='];
        $form['elements'][] = [
            'type'    => 'Label',
            'caption' => 'NUKI Configurator - A project of Ulrich Bittner'];
        $form['elements'][] = [
            'type'    => 'Label',
            'caption' => ' '];
        $form['elements'][] = [
            'type'    => 'SelectCategory',
            'name'    => 'CategoryID',
            'caption' => 'Category'];
        $form['actions'][] = [
            'type'     => 'Configurator',
            'name'     => 'NUKI Configuration',
            'rowCount' => 10,
            //'add' => false,
            'delete' => true,
            'sort'   => [
                'column'    => 'SmartLockName',
                'direction' => 'ascending'],
            'columns' => [
                ['caption' => 'Smart Lock name', 'name' => 'SmartLockName', 'width' => 'auto'],
                ['caption' => 'Smart Lock ID', 'name' => 'SmartLockID', 'width' => '300px'],
                ['caption' => 'Firmware version', 'name' => 'FirmwareVersion', 'width' => '300px']],
            'values' => $this->GetListConfiguration()];
        $jsonForm = json_encode($form);
        $this->SendDebug('FORM', $jsonForm, 0);
        $this->SendDebug('FORM', json_last_error_msg(), 0);
        return $jsonForm;
    }

    // ToDo: change to private again
    public function GetListConfiguration(): array
    {
        // Get already existing Smart Locks
        $smartLockDevices = IPS_GetInstanceListByModuleID('{37C54A7E-53E0-4BE9-BE26-FB8C2C6A3D14}');
        $this->SendDebug('ExistingDevices', json_encode($smartLockDevices), 0);
        // Get available Smart Locks from the Bridge
        // ToDo: Check if Status = 102 (active)
        $availableDevices = NUKI_GetSmartLocks(IPS_GetInstance($this->InstanceID)['ConnectionID']);
        // ToDo: check if we can get the data via socket
        //$availableDevices = $this->SendData('GetSmartLocks');
        $this->SendDebug('AvailableDevices', $availableDevices, 0);
        if (!empty($availableDevices)) {
            $devices = json_decode($availableDevices, true);
            $this->SendDebug('AvailableDevices', json_encode($devices), 0);
        } else {
            $devices = null;
        }
        if (empty($devices)) {
            return [];
        }
        // Prepare data for configuration list
        $configurationlist = [];
        foreach ($devices as $key => $device) {
            $instanceID = 0;
            $smartLockName = $device['name'];
            $smartLockID = $device['nukiId'];
            $this->SendDebug('1. SmartLockID', $smartLockID, 0);
            $firmwareVersion = $device['firmwareVersion'];
            foreach ($smartLockDevices as $smartLockDevice) {
                $smartLockUID = IPS_GetProperty($smartLockDevice, 'SmartLockUID');
                $this->SendDebug('2. SmartLockUID', $smartLockUID, 0);
                if ($smartLockID === $smartLockUID) {
                    //&& (IPS_GetInstance($smartLockDevice)['ConnectionID'] === $parentID)) {
                    $instanceID = $smartLockDevice;
                    $this->SendDebug('3. InstanceID', $instanceID, 0);
                } else {
                    $this->SendDebug('3. InstanceID', 'nicht gefunden', 0);
                }
            }
            $configurationlist[] = [
                'instanceID'      => $instanceID,
                'SmartLockName'   => $smartLockName,
                'SmartLockID'     => $smartLockID,
                'FirmwareVersion' => $firmwareVersion,

                'create' => [
                    'moduleID'      => '{37C54A7E-53E0-4BE9-BE26-FB8C2C6A3D14}',
                    'configuration' => [
                        'SmartLockUID'  => $smartLockID,
                        'SmartLockName' => $smartLockName],
                    'location' => $this->GetCategoryPath($this->ReadPropertyInteger('CategoryID'))]];
        }
        return $configurationlist;
    }

    private function GetCategoryPath(int $CategoryID): array
    {
        if ($CategoryID === 0) {
            return [];
        }
        $path[] = IPS_GetName($CategoryID);
        $parentID = IPS_GetObject($CategoryID)['ParentID'];
        while ($parentID > 0) {
            $path[] = IPS_GetName($parentID);
            $parentID = IPS_GetObject($parentID)['ParentID'];
        }
        return array_reverse($path);
    }
}
