<?php
//src/IMDC/TerpTubeBundle/Command/CreateTerpTubeUserCommand.php

namespace IMDC\TerpTubeBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use FOS\UserBundle\Model\User;

/**
 * @author Matthieu Bontemps <matthieu@knplabs.com>
 * @author Thibault Duplessis <thibault.duplessis@gmail.com>
 * @author Luis Cordova <cordoval@gmail.com>
 * 
 * This is an adaptation of the FOSUserBundle fos:user:create command
 * suited to the IMDC TerpTubeBundle
 *
 * @revisor Paul Church <pchurch@ryerson.ca>
 */
class CreateUserCommand extends ContainerAwareCommand
{
    /**
     * @see Command
     */
    protected function configure()
    {
        $this
        ->setName('imdc:user:createNew')
        ->setDescription('Create a user.')
        ->setDefinition(array(
            new InputArgument('username', InputArgument::REQUIRED, 'The username'),
            new InputArgument('email', InputArgument::REQUIRED, 'The email'),
            new InputArgument('password', InputArgument::REQUIRED, 'The password'),
            new InputArgument('firstname', InputArgument::REQUIRED, 'The first name of the user profile'),
            new InputArgument('lastname', InputArgument::REQUIRED, 'The last name of the user profile'),
            new InputArgument('city', InputArgument::REQUIRED, 'The city of the user profile'),
            new InputArgument('country', InputArgument::REQUIRED, 'The country of the user profile'),
            new InputOption('super-admin', null, InputOption::VALUE_NONE, 'Set the user as super admin'),
            new InputOption('inactive', null, InputOption::VALUE_NONE, 'Set the user as inactive'),
        ))
        ->setHelp(<<<EOT
The <info>fos:user:create</info> command creates a user:

  <info>php app/console fos:user:create matthieu</info>

This interactive shell will ask you for an email, password, first name, last name, city, and then country.

You can alternatively specify arguments:

  <info>php app/console fos:user:create matthieu matthieu@example.com mypassword Matthieu Fakelastname Toronto Canada</info>

You can create a super admin via the super-admin flag:

  <info>php app/console fos:user:create admin --super-admin</info>

You can create an inactive user (will not be able to log in):

  <info>php app/console fos:user:create thibault --inactive</info>

EOT
        );
    }

    /**
     * @see Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $username   = $input->getArgument('username');
        $email      = $input->getArgument('email');
        $password   = $input->getArgument('password');
        $firstname  = $input->getArgument('firstname');
        $lastname   = $input->getArgument('lastname');
        $city       = $input->getArgument('city');
        $country    = $input->getArgument('country');
        $inactive   = $input->getOption('inactive');
        $superadmin = $input->getOption('super-admin');

        $manipulator = $this->getContainer()->get('imdc.utils.user_manipulator');
        $manipulator->create($username, $password, $email, $firstname, $lastname, $city, $country, !$inactive, $superadmin);
                
        $output->writeln(sprintf('Created user <comment>%s</comment>', $username));
    }

    /**
     * @see Command
     */
    protected function interact(InputInterface $input, OutputInterface $output)
    {
        if (!$input->getArgument('username')) {
            $username = $this->getHelper('dialog')->askAndValidate(
                $output,
                'Please choose a username:',
                function($username) {
                    if (empty($username)) {
                        throw new \Exception('Username can not be empty');
                    }

                    return $username;
                }
            );
            $input->setArgument('username', $username);
        }

        if (!$input->getArgument('email')) {
            $email = $this->getHelper('dialog')->askAndValidate(
                $output,
                'Please choose an email:',
                function($email) {
                    if (empty($email)) {
                        throw new \Exception('Email can not be empty');
                    }

                    return $email;
                }
            );
            $input->setArgument('email', $email);
        }

        if (!$input->getArgument('password')) {
            $password = $this->getHelper('dialog')->askAndValidate(
                $output,
                'Please choose a password:',
                function($password) {
                    if (empty($password)) {
                        throw new \Exception('Password can not be empty');
                    }

                    return $password;
                }
            );
            $input->setArgument('password', $password);
        }
        
        if (!$input->getArgument('firstname')) {
            $firstname = $this->getHelper('dialog')->askAndValidate(
                $output,
                'Please choose a first name:',
                function($firstname) {
                    if (empty($firstname)) {
                        throw new \Exception('First name can not be empty');
                    }
        
                    return $firstname;
                }
            );
            $input->setArgument('firstname', $firstname);
        }
        
        if (!$input->getArgument('lastname')) {
            $lastname = $this->getHelper('dialog')->askAndValidate(
                $output,
                'Please choose a last name:',
                function($lastname) {
                    if (empty($lastname)) {
                        throw new \Exception('Last name can not be empty');
                    }
        
                    return $lastname;
                }
            );
            $input->setArgument('lastname', $lastname);
        }
        
        if (!$input->getArgument('city')) {
            $city = $this->getHelper('dialog')->askAndValidate(
                $output,
                'Please choose a city:',
                function($city) {
                    if (empty($city)) {
                        throw new \Exception('City can not be empty');
                    }
        
                    return $city;
                }
            );
            $input->setArgument('city', $city);
        }
        
        if (!$input->getArgument('country')) {
            $country = $this->getHelper('dialog')->askAndValidate(
                $output,
                'Please choose a country:',
                function($country) {
                    if (empty($country)) {
                        throw new \Exception('Country can not be empty');
                    }
        
                    return $country;
                }
            );
            $input->setArgument('country', $country);
        }
        
    }
}