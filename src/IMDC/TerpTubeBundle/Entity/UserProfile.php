<?php

namespace IMDC\TerpTubeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UserProfile
 */
class UserProfile
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $firstName;

    /**
     * @var string
     */
    private $lastName;

    /**
     * @var string
     */
    private $middleName;


    /**
     * @var string
     */
    private $city;

    /**
     * @var string
     */
    private $country;

    /**
     * @var string
     */
    private $gender;

    /**
     * @var string
     */
    private $skypeName;

    /**
     * @var boolean
     */
    private $interestedInMentoredByMentor;

    /**
     * @var boolean
     */
    private $interestedInMentoredByInterpreter;

    /**
     * @var boolean
     */
    private $interestedInMentoringMentor;

    /**
     * @var boolean
     */
    private $interestedInMentoredingInterpreter;

    /**
     * @var \IMDC\TerpTubeBundle\Entity\Media
     */
    private $avatar;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $languages;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->languages = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set firstName
     *
     * @param string $firstName
     * @return UserProfile
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
    
        return $this;
    }

    /**
     * Get firstName
     *
     * @return string 
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * Set lastName
     *
     * @param string $lastName
     * @return UserProfile
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
    
        return $this;
    }

    /**
     * Get lastName
     *
     * @return string 
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * Set middleName
     *
     * @param string $middleName
     * @return UserProfile
     */
    public function setMiddleName($middleName)
    {
        $this->middleName = $middleName;
    
        return $this;
    }

    /**
     * Get middleName
     *
     * @return string 
     */
    public function getMiddleName()
    {
        return $this->middleName;
    }


    /**
     * Set city
     *
     * @param string $city
     * @return UserProfile
     */
    public function setCity($city)
    {
        $this->city = $city;
    
        return $this;
    }

    /**
     * Get city
     *
     * @return string 
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Set country
     *
     * @param string $country
     * @return UserProfile
     */
    public function setCountry($country)
    {
        $this->country = $country;
    
        return $this;
    }

    /**
     * Get country
     *
     * @return string 
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * Set gender
     *
     * @param string $gender
     * @return UserProfile
     */
    public function setGender($gender)
    {
        $this->gender = $gender;
    
        return $this;
    }

    /**
     * Get gender
     *
     * @return string 
     */
    public function getGender()
    {
        return $this->gender;
    }

    /**
     * Set skypeName
     *
     * @param string $skypeName
     * @return UserProfile
     */
    public function setSkypeName($skypeName)
    {
        $this->skypeName = $skypeName;
    
        return $this;
    }

    /**
     * Get skypeName
     *
     * @return string 
     */
    public function getSkypeName()
    {
        return $this->skypeName;
    }

    /**
     * Set interestedInMentoredByMentor
     *
     * @param boolean $interestedInMentoredByMentor
     * @return UserProfile
     */
    public function setInterestedInMentoredByMentor($interestedInMentoredByMentor)
    {
        $this->interestedInMentoredByMentor = $interestedInMentoredByMentor;
    
        return $this;
    }

    /**
     * Get interestedInMentoredByMentor
     *
     * @return boolean 
     */
    public function getInterestedInMentoredByMentor()
    {
        return $this->interestedInMentoredByMentor;
    }

    /**
     * Set interestedInMentoredByInterpreter
     *
     * @param boolean $interestedInMentoredByInterpreter
     * @return UserProfile
     */
    public function setInterestedInMentoredByInterpreter($interestedInMentoredByInterpreter)
    {
        $this->interestedInMentoredByInterpreter = $interestedInMentoredByInterpreter;
    
        return $this;
    }

    /**
     * Get interestedInMentoredByInterpreter
     *
     * @return boolean 
     */
    public function getInterestedInMentoredByInterpreter()
    {
        return $this->interestedInMentoredByInterpreter;
    }

    /**
     * Set interestedInMentoringMentor
     *
     * @param boolean $interestedInMentoringMentor
     * @return UserProfile
     */
    public function setInterestedInMentoringMentor($interestedInMentoringMentor)
    {
        $this->interestedInMentoringMentor = $interestedInMentoringMentor;
    
        return $this;
    }

    /**
     * Get interestedInMentoringMentor
     *
     * @return boolean 
     */
    public function getInterestedInMentoringMentor()
    {
        return $this->interestedInMentoringMentor;
    }

    /**
     * Set interestedInMentoredingInterpreter
     *
     * @param boolean $interestedInMentoredingInterpreter
     * @return UserProfile
     */
    public function setInterestedInMentoredingInterpreter($interestedInMentoredingInterpreter)
    {
        $this->interestedInMentoredingInterpreter = $interestedInMentoredingInterpreter;
    
        return $this;
    }

    /**
     * Get interestedInMentoredingInterpreter
     *
     * @return boolean 
     */
    public function getInterestedInMentoredingInterpreter()
    {
        return $this->interestedInMentoredingInterpreter;
    }

    /**
     * Set avatar
     *
     * @param \IMDC\TerpTubeBundle\Entity\Media $avatar
     * @return UserProfile
     */
    public function setAvatar(\IMDC\TerpTubeBundle\Entity\Media $avatar = null)
    {
        $this->avatar = $avatar;
    
        return $this;
    }

    /**
     * Get avatar
     *
     * @return \IMDC\TerpTubeBundle\Entity\Media 
     */
    public function getAvatar()
    {
        return $this->avatar;
    }

    /**
     * Add languages
     *
     * @param \IMDC\TerpTubeBundle\Entity\Language $languages
     * @return UserProfile
     */
    public function addLanguage(\IMDC\TerpTubeBundle\Entity\Language $languages)
    {
        $this->languages[] = $languages;
    
        return $this;
    }

    /**
     * Remove languages
     *
     * @param \IMDC\TerpTubeBundle\Entity\Language $languages
     */
    public function removeLanguage(\IMDC\TerpTubeBundle\Entity\Language $languages)
    {
        $this->languages->removeElement($languages);
    }

    /**
     * Get languages
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getLanguages()
    {
        return $this->languages;
    }
    /**
     * @var string
     */
    private $textBio;


    /**
     * Set textBio
     *
     * @param string $textBio
     * @return UserProfile
     */
    public function setTextBio($textBio)
    {
        $this->textBio = $textBio;
    
        return $this;
    }

    /**
     * Get textBio
     *
     * @return string 
     */
    public function getTextBio()
    {
        return $this->textBio;
    }
    /**
     * @var boolean
     */
    private $interestedInMentoringSignLanguage;


    /**
     * Set interestedInMentoringSignLanguage
     *
     * @param boolean $interestedInMentoringSignLanguage
     * @return UserProfile
     */
    public function setInterestedInMentoringSignLanguage($interestedInMentoringSignLanguage)
    {
        $this->interestedInMentoringSignLanguage = $interestedInMentoringSignLanguage;
    
        return $this;
    }

    /**
     * Get interestedInMentoringSignLanguage
     *
     * @return boolean 
     */
    public function getInterestedInMentoringSignLanguage()
    {
        return $this->interestedInMentoringSignLanguage;
    }
    /**
     * @var string
     */
    private $birthDate;


    /**
     * Set birthDate
     *
     * @param string $birthDate
     * @return UserProfile
     */
    public function setBirthDate($birthDate)
    {
        $this->birthDate = $birthDate;
    
        return $this;
    }

    /**
     * Get birthDate
     *
     * @return string 
     */
    public function getBirthDate()
    {
        return $this->birthDate;
    }
    /**
     * @var boolean
     */
    private $interestedInMentoringInterpreter;


    /**
     * Set interestedInMentoringInterpreter
     *
     * @param boolean $interestedInMentoringInterpreter
     * @return UserProfile
     */
    public function setInterestedInMentoringInterpreter($interestedInMentoringInterpreter)
    {
        $this->interestedInMentoringInterpreter = $interestedInMentoringInterpreter;
    
        return $this;
    }

    /**
     * Get interestedInMentoringInterpreter
     *
     * @return boolean 
     */
    public function getInterestedInMentoringInterpreter()
    {
        return $this->interestedInMentoringInterpreter;
    }
}