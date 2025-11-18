<?php

//Empêche injection SQL
function esc($str)
{
    global $connexion;
    return mysqli_real_escape_string($connexion, $str);
}

// Création de la classe User
class User
{
    // Attributs
    private $id;
    public $login;
    public $email;
    public $firstname;
    public $lastname;

    private $connexion;
    private $connected = false;

    // Constructeur
    public function __construct($connexion)
    {

        $this->connexion = $connexion;
    }

    // Crée un utilisateur

    public function register($login, $password, $email, $firstname, $lastname)
    {
        $login = esc($login);
        $email = esc($email);
        $firstname = esc($firstname);
        $lastname = esc($lastname);
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        // Insertion dans la base de données
        $sql = "INSERT INTO utilisateur (login, password, email, firstname, lastname)
                VALUE ('$login', '$passwordHash','$email','$firstname', '$lastname')";

        if (mysqli_query($this->connexion, $sql)) {

            // Connection automatiquement l'utilisateur
            $this->connect($login, $password);
            return $this->getAllinfos();
        } else {
            echo "Erreur lors de l'inscription : " . mysqli_errno($this->connexion);
            return false;
        }
    }
    // connexion
    public function connect($login, $password)
    {
        // Échappe les caractères spéciaux pour éviter les injections SQL
        $login = esc($login);
        $sql = "SELECT * FROM utilisateur WHERE login = '$login'";
        $result = mysqli_query($this->connexion, $sql); //Appelle la base de donnée

        if ($result && mysqli_num_rows($result) > 0) {
            $data = mysqli_fetch_assoc($result);

            // Vérifie le mot de passe
            if (password_verify($password, $data['password'])) {
                $this->id = $data['id'];
                $this->login = $data['login'];
                $this->email = $data['email'];
                $this->firstname = $data['firstname'];
                $this->lastname = $data['lastname'];
                $this->connected = true;
                return true;
            }
        }
        $this->connected = false;
        return false;
    }
    //Déconnexion
    public function disconnect()
    {
        $this->id = null;
        $this->login = null;
        $this->email = null;
        $this->firstname = null;
        $this->lastname = null;
        $this->connected = false;
    }

    // Supprime utilisateur et déconnecte
    public function delete()
    {
        if ($this->connected && $this->id) {
            $sql = "DELETE FROM utilisateur WHERE id = $this->id";
            if (mysqli_query($this->connexion, $sql)) {
                $this->disconnect();
                return true;
            }
        }
        return false;
    }

    //met à jour l'utilisateur
    public function update($login, $password, $email, $firstname, $lastname)
    {
        if (!$this->connected || !$this->id) return false;

        $login = esc($login);
        $email = esc($email);
        $firstname = esc($firstname);
        $lastname = esc($lastname);
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        // Met à jour dans la base de données
        $sql = "UPDATE utilisateur
        SET login = '$login', password ='$passwordHash', email = $email', firstname =$firstname', lastname = '$lastname'
        WHERE id = $this->id";


        if (mysqli_query($this->connexion, $sql)) {
            // met à jour les attributs de l'objet
            $this->login = $login;
            $this->email = $email;
            $this->firstname = $firstname;
            $this->lastname = $lastname;
            return true;
        }
        return false;
    }

    // Vérifie si connectéé
    public function isConnected()
    {
        return $this->connected;
    }

    // Retourne toutes les informations
    public function getAllInfos()
    {
        if ($this->connected) {
            return [
                "id" => $this->id,
                "login" => $this->login,
                "email" => $this->firstname,
                "lastname" => $this->lastname
            ];
        }
        return null;
    }

    //Getters
    public function getLogin()
    {
        return $this->login;
    }
    public function getEmail()
    {
        return $this->email;
    }
    public function getFirstname()
    {
        return $this->firstname;
    }
    public function getLastname()
    {
        return $this->lastname;
    }

    // Lis un utilisateur par son ID
    public function read($id)
    {
        $id = (int)$id;
        $sql = "SELECT * FROM utilisateur WHERE id = $id";
        $result = mysqli_query($this->connexion, $sql);

        // Remplit les attributs si trouvé
        if ($result && mysqli_num_rows($result) > 0) {

            $data = mysqli_fetch_assoc($result);

            $this->id = $data['id'];
            $this->login = $data['login'];
            $this->email = $data['email'];
            $this->firstname = $data['firstname'];
            $this->lastname = $data['lastname'];
            return $data;
        }
        return null;
    }
}
// Exemple d'utilisation
$connexion = mysqli_connect("localhost", "root", "", "classes");
$u1 = new User($connexion);
$u1->register("Micka", "Mdp", "mickaelayilan@hotmail.com", "mickael", "Ayilan");
echo "Bonjour je suis un monstre" . $u1->firstname . "!";
