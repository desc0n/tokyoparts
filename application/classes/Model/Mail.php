<?php

/**
 * Class Model_Mail
 */
class Model_Mail extends Kohana_Model
{
    /**
     * This is a reference to the Imap stream generated by 'imap_open'.
     *
     * @var resource
     */
    private $imapStream;

    /**
     * This value defines the encoding we want the email message to use.
     *
     * @var string
     */
    public static $charset = 'UTF-8';

    /**
     * @var array
     */
    private $options = [];

    public function __construct()
    {
        $this->options = Arr::get(Kohana::$config->load('email')->as_array(), 'options', []);
    }

    /**
     * @return resource
     */
    public function getImapStream()
    {
        if (empty($this->imapStream))
            $this->setImapStream();
        return $this->imapStream;
    }

    private function setImapStream()
    {
        $serverString = '{imap.gmail.com:993/imap/ssl/novalidate-cert}';
        if (!empty($this->imapStream)) {
            if (!imap_reopen($this->imapStream, $serverString, 0, 1))
                throw new \RuntimeException(imap_last_error());
        } else {
            $imapStream = imap_open($serverString, $this->options['username'], $this->options['password'], 0, 1);
            if ($imapStream === false)
                throw new \RuntimeException(imap_last_error());
            $this->imapStream = $imapStream;
        }
    }

    /**
     * @param  string   $criteria
     * @param  null|int $limit
     *
     * @return array
     */
    public function search($criteria = 'ALL', $limit = null)
    {
        $messagesIds = [];

        if ($results = imap_search($this->getImapStream(), $criteria, SE_UID)) {
            if (isset($limit) && count($results) > $limit) {
                $results = array_slice($results, 0, $limit);
            }

            foreach ($results as $messageId) {
                $messagesIds[] = $messageId;
            }
        }

        return $messagesIds;
    }

    /**
     * @param int $supplierId
     * @param array $settings
     * @param array $messagesIds
     */
    public function loadAttachmentData($supplierId, $settings, $messagesIds)
    {
        $issetMessagesId = DB::select()
            ->from('mail__messages')
            ->where('supplier_id', '=', $supplierId)
            ->execute()
            ->as_array(null, 'uid')
        ;

        foreach ($messagesIds as $messageId) {
            if (in_array($messageId, $issetMessagesId, null)) {
                continue;
            }

            $structure = $this->getStructure($messageId);

            if (isset($structure->parts)) {
                foreach ($structure->parts as $id => $part) {
                    $parameters = $this->getParametersFromStructure($part);

                    if (isset($parameters['filename'])) {
                        if ($this->saveAs($settings, $part, $messageId, ($id + 1), preg_replace('/[=?]+/', '', $parameters['filename']))) {
                            DB::insert('mail__messages', ['supplier_id', 'uid', 'filename', 'created_at'])
                                ->values([$supplierId, $messageId, $parameters['filename'], DB::expr('NOW()')])
                                ->execute()
                            ;
                        }
                    }
                }
            }
        }
    }

    /**
     * This function saves the attachment to the exact specified location.
     *
     * @param  array $settings
     * @param  \stdClass $structure
     * @param  int $messageId
     * @param  string $filename
     *
     * @return bool
     */
    public function saveAs($settings, $structure, $messageId, $partId, $filename)
    {
        $path = 'public/prices/' . $settings['dir'] . '/' . $filename;
        $dirname = dirname($path);

        if (file_exists($path)) {
            if (!is_writable($path)) {
                return false;
            }
        } elseif (!is_dir($dirname) || !is_writable($dirname)) {
            return false;
        }
        if (($filePointer = fopen($path, 'w')) == false) {
            return false;
        }
        switch ($structure->encoding) {
            case 3: //base64
                $streamFilter = stream_filter_append($filePointer, 'convert.base64-decode', STREAM_FILTER_WRITE);
                break;
            case 4: //quoted-printable
                $streamFilter = stream_filter_append($filePointer, 'convert.quoted-printable-decode', STREAM_FILTER_WRITE);
                break;
            default:
                $streamFilter = null;
        }
        // Fix an issue causing server to throw an error
        // See: https://github.com/tedious/Fetch/issues/74 for more details
        imap_fetchbody($this->imapStream, $messageId, $partId ?: 1, FT_UID);
        $result = imap_savebody($this->imapStream, $filePointer, $messageId, $partId ?: 1, FT_UID);

        if ($streamFilter) {
            stream_filter_remove($streamFilter);
        }

        fclose($filePointer);

        if ($settings['archive']) {
            $zip = new ZipArchive;

            if ($zip->open($path) === TRUE) {
                $zip->renameIndex(0, $settings['file']);
                $zip->extractTo($dirname, $zip->getNameIndex(0));
                $zip->close();
            }
        }

        return $result;
    }

    /**
     * @param  int      $messageId
     * @return \stdClass
     */
    public function getStructure($messageId)
    {
        return imap_fetchstructure($this->imapStream, $messageId, FT_UID);
    }

    /**
     * Takes in a section structure and returns its parameters as an associative array.
     *
     * @param  \stdClass $structure
     * @return array
     */
    public function getParametersFromStructure($structure)
    {
        $parameters = array();
        if (isset($structure->parameters)) {
            foreach ($structure->parameters as $parameter) {
                $parameters[strtolower($parameter->attribute)] = $parameter->value;
            }
        }

        if (isset($structure->dparameters)) {
            foreach ($structure->dparameters as $parameter) {
                $parameters[strtolower($parameter->attribute)] = $parameter->value;
            }
        }

        return $parameters;
    }
}