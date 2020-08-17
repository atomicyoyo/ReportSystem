<?php

namespace ConstructStudios\Report\forms;

use ConstructStudios\PGToDiscord\PGTD;
use ConstructStudios\Report\libs\dktapps\pmforms\CustomForm;
use ConstructStudios\Report\libs\dktapps\pmforms\CustomFormResponse;
use ConstructStudios\Report\libs\dktapps\pmforms\element\Input;
use ConstructStudios\Report\libs\dktapps\pmforms\element\Dropdown;
use ConstructStudios\Report\libs\dktapps\pmforms\element\Label;
use ConstructStudios\Report\libs\dktapps\pmforms\element\Toggle;
use ConstructStudios\Report\Report;
use pocketmine\Player;

class PlayerReportForm extends CustomForm
{
    public $pnarr = [];

    /**
     * PlayerReportForm constructor.
     */
    public function __construct()
    {
        $title = "§cReport a Player!";
        foreach (Report::getInstance()->getServer()->getOnlinePlayers() as $player)
        {
            $this->pnarr[$player->getName()] = $player->getName();
        }
        $elements = [
            new Input("reportname", "Report name", "Hacker...."),
            new Dropdown("playername", "Choose a Player", $this->pnarr),
            new Input("desc", "Description - What happened?"),
            new Input("notizen", "Extra Notes"),
            new Label("l", "\n"),
            new Toggle("exit", "§cDiscard Report", 0)
        ];
        parent::__construct($title, $elements, function (Player $player, CustomFormResponse $response) : void {
            if($response->getBool("exit") == false){
                $reportname = $response->getString("reportname");
                $pint = $response->getInt("playername");
                $playername = array_keys($this->pnarr)[$pint];
                $desc = $response->getString("desc");
                $notizen = $response->getString("notizen");

                Report::getInstance()->saveReport($reportname, $player->getName(), $playername, $desc, $notizen);
                $player->sendMessage(Report::getInstance()->prefix . "§eYour report was sent!");
                Report::getInstance()->sendReportToMod();

                if(Report::getInstance()->discord){
                    PGTD::getInstance()->sendMessage(array("message" => "NEW Report! " . $player->getName() . " reported $playername for $desc!"), PGTD::TYPE_PLUGIN);
                }
            }else{
                $player->removeAllWindows();
                $player->sendMessage(Report::getInstance()->prefix . "§cReport was not send and got deleted!");
            }
            return;
        });
    }
}
