<?php

    namespace thecodeisbae\FTPManager;

    class FTPManager
    {
        public static $ftpHost; /* L'hote FTP */
        public static $ftpPort; /* Le port de connexion FTP */
        public static $ftpUser; /* L'utilisateur FTP */
        public static $ftpPassword; /* Le mot de passe FTP */
        public static $ftpResource; /* Une ressource FTP */
        protected static $ftpConnect; /* Un flag booleen pour connaitre le statut de la connexion FTP */

        /**
         * This function is used to initialize an FTP Connection
         */
        static function init() : void /* Initier une connexion FTP */
        {
            self::$ftpResource = \ftp_connect(self::$ftpHost);
        }

        /**
         * This function is used to close an FTP Connection
         */
        static function close() : void /* Terminer la session ouverte */
        {
            self::$ftpConnect = \ftp_close(self::$ftpResource);
        }

        /**
         * This function is used to authenticate user on FTP Server
         */
        static function connect() : void /* S'authentifier sur le serveur */
        {
            self::$ftpConnect = \ftp_login(self::$ftpResource,self::$ftpUser,self::$ftpPassword);
            self::passive(true);
        }

        /**
         * This function is used to switch passive mode status of an FTP Connection
         */
        static function passive($statut) : void /* Changer le mode passif */
        {
            \ftp_pasv(self::$ftpResource,$statut);
        }

        /**
         * This function is used to retrieve the contents of a folder in raw format
         */
        static function rawList($directory,$recursive = false) : array /*    */ 
        {
            if(self::$ftpConnect)
            {
                return \ftp_rawlist(self::$ftpResource,$directory,$recursive);
            }
            return ['code'=>0,'message'=>'Connexion FTP requise ou connexion perdue'];
        }

        /**
         * This function is used to retrieve the contents of a folder in array format with the keys
         */
        static function arrayList($directory) : array /* Recuperer le contenu d'un dossier en format array avec les clés */ 
        {
            if(self::$ftpConnect)
            {
                return \ftp_mlsd(self::$ftpResource,$directory);
            }
            return ['code'=>0,'message'=>'Connexion FTP requise ou connexion perdue'];
        } 

        /**
         * This function is used to retrieve the contents of a folder in array format with only names
         */
        static function list($directory) : array /* Recuperer le contenu d'un dossier en format array avec uniquement les noms */ 
        {
            if(self::$ftpConnect)
            {
                return \ftp_nlist(self::$ftpResource,$directory);
            }
            return ['code'=>0,'message'=>'Connexion FTP requise ou connexion perdue'];
        }

        /**
         * This function is used to Find a file in a specified folder
         * @param string $needle is the searched file
         */
        static function find($needle,$path) : array /* Rechercher un fichier dans d'un dossier */ 
        {
            foreach(self::list($path) as $value)
            {
                if($value == $needle)
                {
                    return ['code'=>0,'message'=>'Ce fichier exite déjà dans cet emplacement'];   
                }
            }
            return ['code'=>1,'message'=>'Aucune correspondance']; 
        }

        /**
         * This function is used to create a directory
         */
        static function createDir($path) : void
        {
            \ftp_mkdir(self::$ftpResource,$path);
        }

        /**
         * This function is used to store a file in the provided path
         * @param string $path is the path of the stored file
         */
        static function store($path,$file) : array /* Sauvegarder un fichier sur le serveur */
        {
            if($path[0] == '/' || $path[0] == '\\')
                $path = substr($path,1);

            if($path[strlen($path)-1] == '/' || $path[0] == '\\')
                $path = substr($path,0,strlen($path)-1);

            $root = '';
            
            $remoteFileName = explode('/',explode('.',$file)[sizeof(explode('.',$file))-2])[sizeof(explode('/',explode('.',$file)[sizeof(explode('.',$file))-2]))-1].'.'.explode('.',$file)[sizeof(explode('.',$file))-1];

            $table = explode('/',$path);
       
            if(sizeof($table))
            {
                foreach($table as $key => $value)
                {   
                    if(self::find($value,$root)['code'])
                    {
                        self::createDir($value);
                        $root .= '/'.$value;
                        \ftp_chdir(self::$ftpResource,$root);
                    }
                    else
                    {             
                        $root .= '/'.$value;       
                        \ftp_chdir(self::$ftpResource,$root);
                    } 

                }
            }

            \ftp_chdir(self::$ftpResource,$root);
            if(\ftp_put(self::$ftpResource,$remoteFileName,$file))
            {
                \ftp_chdir(self::$ftpResource,'/');
                return ['code'=>1,'message'=>'Fichier envoyé avec succès','chemin'=>$root.'/'.$remoteFileName];
            }

            \ftp_chdir(self::$ftpResource,'/');
            return ['code'=>0,'mesage'=>'Une erreur s\'est produite'];
        }

        static function download($filepath)
        {
            if($filepath[0] == '/' || $filepath[0] == '\\')
            $filepath = substr($filepath,1);

            if($filepath[strlen($filepath)-1] == '/' || $filepath[0] == '\\')
                $filepath = substr($filepath,0,strlen($filepath)-1);

            $root = '/';
            
            $table = explode('/',$filepath);
            if(sizeof($table))
            {
                foreach($table as $key => $value)
                {   
                    if($key > sizeof($table)-2)
                        break;
                    if( !(self::find($value,$root)['code']) )
                    {
                        $root .= '/'.$value;
                        \ftp_chdir(self::$ftpResource,$root);
                    }
                    else
                    {             
                        return ['code'=>0,'message'=>'Le chemin fourni est erroné'];
                    } 

                }
            }

            if(\ftp_get(self::$ftpResource,$table[$key],$table[$key]))
                return ['code'=>1,'message'=>'Fichier téléchargé avec succès'];

            return ['code'=>0,'message'=>'Une erreur s\'est produite'];
        }

    }
