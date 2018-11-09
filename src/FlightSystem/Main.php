<?php

namespace FlightSystem;

use pocketmine\plugin\PluginBase;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\level\particle\EntityFlameParticle;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\item\Item;
use pocketmine\event\Listener;
use pocketmine\math\Vector3; 
use jojoe77777\FormAPI;
use pocketmine\Player;
use pocketmine\Server;
use FlightSystem\Main;

class Main extends PluginBase implements Listener {
    
    public function onEnable(){
        $this->getLogger()->info("running flightsystemui");
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->eco = $this->getServer()->getPluginManager()->getPlugin("EconomyAPI");
		
		@mkdir($this->getDataFolder());
        $this->saveDefaultConfig();
        $this->getResource("config.yml");
    }


    public function checkDepends(){
        $this->formapi = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
        if(is_null($this->formapi)){
            $this->getLogger()->info("disabled flightsystemui, Please install FormAPI, EconomyAPI");
            $this->getPluginLoader()->disablePlugin($this);
        }
    }

    public function onCommand(CommandSender $sender, Command $cmd, string $label, array $args):bool
    {
        switch($cmd->getName()){
        case "flight":
        if(!$sender instanceof Player){
                $sender->sendMessage("§cThis command can't be used here.");
                return true;
        }
        $api = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
        $form = $api->createSimpleForm(function (Player $sender, $data){
            $result = $data;
            if ($result == null) {
            }
            switch ($result) {
                    case 0:
                    $sender->sendMessage($this->getConfig()->get("cancelled"));
                        break;
                    case 1:
                    $this->FlySystem($sender);
                        break;
                    case 2:
                    $this->SpectatorMode($sender);
                        break;
                    case 3:
                    $this->leap($sender);
                        break;
            }
        });
        $form->setTitle("§lFlight System");
        $form->setContent("Please select.");
        $form->addButton("§4Exit", 0);
        $form->addButton("Fly Mode", 1);
        $form->addButton("Spectator Mode", 2);
        $form->addButton("leap", 3);
        $form->sendToPlayer($sender);
        break;
        case "fly":
        if(!($sender instanceof Player)){
                $sender->sendMessage("This command can't be used here.");
                return true;
        }
        $api = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
        $form = $api->createSimpleForm(function (Player $sender, $data){
            $result = $data;
            if ($result == null) {
            }
            switch ($result) {
                    case 0:
                    $sender->sendMessage($this->getConfig()->get("fly.exited"));
                        break;
                    case 1:
                    $sender->setAllowFlight(true);
                    $sender->sendMessage($this->getConfig()->get("fly.true"));
                        break;
                    case 2:
                    $sender->setAllowFlight(false);
                    $sender->sendMessage($this->getConfig()->get("fly.false"));
                        break;
            }
        });
        $form->setTitle("§lFly Access");
        $form->setContent($this->getConfig()->get("fly.access.content"));
        $form->addButton("Exit", 0);
        $form->addButton("On", 1);
        $form->addButton("Off", 2);
        $form->sendToPlayer($sender);
        break;
        case "leap":
         if(!($sender instanceof Player)){
                $sender->sendMessage("This command can't be used here.");
                return true;
        }
        $sender->getInventory()->addItem(Item::get(288, 0, 1));
        $sender->sendMessage($this->getConfig()->get("leap.give"));
        break;
        case "spectate":
        if(!($sender instanceof Player)){
                $sender->sendMessage("This command can't be used here.");
                return true;
        }
        $api = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
        $form = $api->createSimpleForm(function (Player $sender, $data){
            $result = $data;
            if ($result == null) {
            }
            switch ($result) {
                    case 0:
                    $sender->sendMessage($this->getConfig()->get("spectate.exited"));
                        break;
                    case 1:
                    $sender->setGamemode(3);
                    $sender->sendMessage($this->getConfig()->get("spectate.true"));
                        break;
                    case 2:
                    $sender->setGamemode(0);
                    $sender->sendMessage($this->getConfig()->get("spectate.false"));
                        break;
            }
        });
        $form->setTitle("§lSpectator Mode Access");
        $form->setContent($this->getConfig()->get("spectate.access.content"));
        $form->addButton("Exit", 0);
        $form->addButton("On", 1);
        $form->addButton("Off", 2);
        $form->sendToPlayer($sender);
        }
        return true;
    }
    public function FlySystem($sender){
        $api = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
        $form = $api->createModalForm(function (Player $sender, $data){
            $result = $data;
            if ($result == null) {
            }
            switch ($result) {
                    case 1:
            $money = $this->eco->myMoney($sender);
            $fly = $this->getConfig()->get("fly.cost");
            if($money >= $fly){

               $this->eco->reduceMoney($sender, $fly);
		    
	       // old code
               //$this->getServer()->dispatchCommand(new ConsoleCommandSender(), "setuperm " . $sender->getName() . " fly.cmd");
	       //   
		    
	       $sender->addAttachment($this, "fly.cmd", true);
               $sender->sendMessage($this->getConfig()->get("fly.success"));
              return true;
            }else{
               $sender->sendMessage($this->getConfig()->get("fly.no.money"));
            }
                        break;
                    case 2:
               $sender->sendMessage($this->getConfig()->get("fly.cancelled"));
                        break;
            }
        });
        $form->setTitle("§lFly Command");
        $form->setContent($this->getConfig()->get("fly.content"));
        $form->setButton1("Confirm", 1);
        $form->setButton2("Cancel", 2);
        $form->sendToPlayer($sender);
    }
    public function leap($sender){
        $api = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
        $form = $api->createModalForm(function (Player $sender, $data){
            $result = $data;
            if ($result == null) {
            }
            switch ($result) {
                    case 1:
            $money = $this->eco->myMoney($sender);
            $fly = $this->getConfig()->get("leap.cost");
            if($money >= $fly){

               $this->eco->reduceMoney($sender, $fly);
		    
	       //old code
               //$this->getServer()->dispatchCommand(new ConsoleCommandSender(), "setuperm " . $sender->getName() . " leap.cmd");
	       //
		    
	       $sender->addAttachment($this, "leap.cmd", true);
               $sender->sendMessage($this->getConfig()->get("leap.success"));
              return true;
            }else{
               $sender->sendMessage($this->getConfig()->get("leap.no.money"));
            }
                        break;
                    case 2:
               $sender->sendMessage($this->getConfig()->get("leap.cancelled"));
                        break;
            }
        });
        $form->setTitle("§lLeap Command");
        $form->setContent($this->getConfig()->get("leap.content"));
        $form->setButton1("Confirm", 1);
        $form->setButton2("Cancel", 2);
        $form->sendToPlayer($sender);
    }
    public function SpectatorMode($sender){
        $api = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
        $form = $api->createModalForm(function (Player $sender, $data){
            $result = $data;
            if ($result == null) {
            }
            switch ($result) {
                    case 1:
            $money = $this->eco->myMoney($sender);
            $fly = $this->getConfig()->get("spectate.cost");
            if($money >= $fly){

               $this->eco->reduceMoney($sender, $fly);
		    
	       // old code
               //$this->getServer()->dispatchCommand(new ConsoleCommandSender(), "setuperm " . $sender->getName() . " spectate.cmd");
	       //
		    
	       $sender->addAttachment($this, "spectate.cmd", true);
               $sender->sendMessage($this->getConfig()->get("spectate.success"));
              return true;
            }else{
               $sender->sendMessage($this->getConfig()->get("spectate.no.money"));
            }
                        break;
                    case 2:
               $sender->sendMessage($this->getConfig()->get("spectate.cancelled"));
                        break;
            }
        });
        $form->setTitle("§lSpectator Mode");
        $form->setContent($this->getConfig()->get("spectate.content"));
        $form->setButton1("Confirm", 1);
        $form->setButton2("Cancel", 2);
        $form->sendToPlayer($sender);
    }
    public function Menu(PlayerInteractEvent $e){
    $p = $e->getPlayer();
    if($p->getInventory()->getItemInHand()->getId() == 288)
    {$p->setMotion(new Vector3(0, 1, 0));
    $this->damage[$p->getName()] = true;
    $v2 = new Vector3($p->getX()-1, $p->getY(), $p->getZ()); 
    $p->getLevel()->addParticle(new EntityFlameParticle($v2));
    }
    }

    public function onDamage(EntityDamageEvent $e){
    $p = $e->getEntity();
    if($p instanceof Player){
    if($e->getCause() == 4){
    if(isset($this->damage[$p->getName()]))
    {$e->setCancelled();
    unset($this->damage[$p->getName()]);}}}}
    
}
?>
