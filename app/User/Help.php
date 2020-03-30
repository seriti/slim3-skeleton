<?php
namespace App\User;

use Psr\Container\ContainerInterface;
use Seriti\Tools\Secure;
use Seriti\Tools\DbInterface;

use Seriti\Tools\IconsClassesLinks;


class Help
{
    use IconsClassesLinks;

    protected $container;
    protected $db;
    protected $table;
    

    public function __construct(DbInterface $db,ContainerInterface $container,$table)
    {
        $this->db = $db;
        $this->container = $container;
        $this->table = $table;
    }

    public function getHelp()
    {
        $html = '<div class="container">';
        
        $sql = 'SELECT id,title,text_html,access '.
               'FROM '.$this->table.' WHERE status <> "HIDE" ORDER BY rank ';
        $topics = $this->db->readSqlArray($sql);
        if($topics == 0) {
            $html .= 'NO valid help topics found';
        } else {
            //remove items that user does not have access to
            
            foreach($topics as $id => $topic) {
                if(!$this->container['user']->checkUserAccess($topic['access'])) {
                    unset($topics[$id]);
                }
            }
            

            $t = 0;

            $html .= '<div class="'.$this->classes['message'].'">'.
                     '<div class="row">';
            foreach($topics as $id => $topic) {
                if(fmod($t,4)==0) $html .= '</div><div class="row">';
                $t++;

                $html .= '<div class="col-sm-3 '.$this->classes['button_plain'].'"><a href="#'.$id.'">'.$topic['title'].'</a></div>';
            }
            $html .= '</div></div>';

            $html .= '<div class="'.$this->classes['file_list'].'">';
            foreach($topics as $id => $topic) {
                //NB: &nbsp; must be there for anchor padding to work !!
                $html .= '<div class="row"><a name="'.$id.'" class="'.$this->classes['anchor'].'">&nbsp;</a>';
                $html .= '<h2>'.$topic['title'].'<a href="#top">'.$this->icons['arrow_up'].'</a></h2>';
                $html .= '<div class="col-sm-12">'.$topic['text_html'].'</div>';
                                
                $html .= '</div>';
            }
            $html .= '</div>';    
        }

        $html .= '</div>';

        return $html;
    }
    
}