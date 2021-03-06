<?php
namespace Novosga;

use \Novosga\SGA;
use \Novosga\Http\Session;
use \Novosga\Http\Cookie;
use \Novosga\Http\Request;
use \Novosga\Http\Response;
use \Novosga\Model\Util\UsuarioSessao;
use \Novosga\Model\Modulo;
use \Novosga\Model\Unidade;
use \Novosga\Util\Arrays;
use \Novosga\Db\DatabaseConfig;

/**
 * SGAContext
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class SGAContext {
    
    private $app;
    private $session;
    private $request;
    private $response;
    private $cookie;
    private $user;
    private $modulo;
    private $database;
    private $parameters = array();
    
    public function __construct(SGA $app, DatabaseConfig $database) {
        $this->app = $app;
        $this->session = new Session();
        $this->cookie = new Cookie();
        $this->request = new Request();
        $this->response = new Response();
        $this->database = $database;
    }
    
    /**
     * @return SGA
     */
    public function app() {
        return $this->app;
    }
    
    /**
     * @return Session
     */
    public function session() {
        return $this->session;
    }
    
    /**
     * @return Cookie
     */
    public function cookie() {
        return $this->cookie;
    }

    /**
     * @return Request
     */
    public function request() {
        return $this->request;
    }
    
    /**
     * @return Response
     */
    public function response() {
        return $this->response;
    }
    
    /**
     * 
     * @return \Novosga\Db\DatabaseConfig
     */
    public function database() {
        return $this->database;
    }

    /**
     * @return UsuarioSessao
     */
    public function getUser() {
        if ($this->user == null) {
            $this->user = $this->session()->getGlobal(SGA::K_CURRENT_USER);
            if ($this->user) {
                $this->user->setEm($this->database()->createEntityManager());
            }
        }
        return $this->user;
    }

    public function setUser(UsuarioSessao $user = null) {
        $this->user = $user;
        $this->session()->setGlobal(SGA::K_CURRENT_USER, $user);
    }

    /**
     * @return Unidade|null
     */
    public function getUnidade() {
        if ($this->getUser()) {
            return $this->getUser()->getUnidade();
        }
        return null;
    }

    public function setUnidade(Unidade $unidade = null) {
        if ($this->getUser()) {
            $this->getUser()->setUnidade($unidade);
            $this->setUser($this->getUser());
        }
    }

    /**
     * @return Modulo
     */
    public function getModulo() {
        if ($this->modulo == null && defined('MODULE')) {
            $query = $this->database->createEntityManager()
                    ->createQuery("SELECT m FROM Novosga\Model\Modulo m WHERE m.chave = :chave");
            $query->setParameter('chave', MODULE);
            $this->modulo = $query->getOneOrNullResult();
            if (!$this->modulo) {
                throw new \Exception(sprintf(_('Módulo "%s" não econtrado.'), MODULE));
            }
        }
        return $this->modulo;
    }

    public function setModule(Modulo $modulo = null) {
        $this->modulo = $modulo;
    }
    
    public function getParameters() {
        return $this->parameters;
    }
    
    public function setParameters(array $params) {
        $this->parameters = $params;
    }
    
    public function getParameter($key) {
        return Arrays::value($this->parameters, $key);
    }
    
    public function setParameter($key, $value) {
        $this->parameters[$key] = $value;
    }
    
}
