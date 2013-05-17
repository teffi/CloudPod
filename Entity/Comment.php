<?php
namespace CloudPod\ClassroomBundle\Entity;

class Comment
{
 	// @var auto integer
    protected $commentID;

	// @var string
	protected $comment;

    //@var string
    protected $commentDate;

	// @var string
	protected $commentUser;

    protected $userpic;
    /**
     * Constructor
     */
    public function __construct()
    {
        date_default_timezone_set('Asia/Manila');    
        $this->commentDate = new \DateTime("now");  
    }
    
   

    /**
     * Get commentID
     *
     * @return integer 
     */
    public function getCommentID()
    {
        return $this->commentID;
    }

    /**
     * Set comment
     *
     * @param string $comment
     * @return Comment
     */
    public function setComment($comment)
    {
        $this->comment = $comment;
    
        return $this;
    }

    /**
     * Get comment
     *
     * @return string 
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * Set commentDate
     *
     * @param \DateTime $commentDate
     * @return Comment
     */
    public function setCommentDate($commentDate)
    {
        $this->commentDate = $commentDate;
    
        return $this;
    }

    /**
     * Get commentDate
     *
     * @return \DateTime 
     */
    public function getCommentDate()
    {
        return $this->commentDate;
    }

    /**
     * Set commentUser
     *
     * @param CloudPod\UserBundle\Entity\User $commentUser
     * @return Comment
     */
    public function setCommentUser(\CloudPod\UserBundle\Entity\User $commentUser = null)
    {
        $this->commentUser = $commentUser;
    
        return $this;
    }

    /**
     * Get commentUser
     *
     * @return CloudPod\UserBundle\Entity\User 
     */
    public function getCommentUser()
    {
        return $this->commentUser;
    }

    public function getUserpic()
    {
        return $this->userpic;
    }

    public function setUserpic($userpic)
    {
        $this->userpic = $userpic;
    
        return $this;
    }

    public function MarkUp()
    {
        // renders the styling and markup of the comment on the page
        return '
        <div class="comment"><span style="font-size:15px;font-weight:bold;"><img src="'.$this->getUserpic().'" class="avatar"/>&nbsp;'.
        $this->getCommentUser()->getUserName().'</span>
         <span class="commentDate">'.$this->commentDate->format('Y-M-d').'</span>
        <div>'.$this->comment.'</div></div>';

    }
}