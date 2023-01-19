<?php
namespace App\Command;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Utils\Validator;
use Doctrine\ORM\EntityManagerInterface;
use RuntimeException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Stopwatch\Stopwatch;
use function Symfony\Component\String\u;

// the name of the command is what users type after "php bin/console"
#[AsCommand(name: 'app:create-user')]
class AddUserCommand extends Command
{
    private SymfonyStyle $io;
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher,
        private Validator $validator,
        private UserRepository $users
    ) {
        parent::__construct();
    }
    protected function configure()
    {

        $this
            ->setHelp($this->getCommandHelp())
            ->addArgument('email', InputArgument::OPTIONAL, 'The email of the new user')
            ->addArgument('role', InputArgument::OPTIONAL, 'Role of the user(ROLE_STUDENT,ROLE_TEACHER)')
            ->addArgument('password', InputArgument::OPTIONAL, 'The plain password of the new user')
            ->addOption('admin', null, InputOption::VALUE_NONE, 'If set, the user is created as an administrator');

    }
    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        // SymfonyStyle is an optional feature that Symfony provides so you can
        // apply a consistent look to the commands of your application.
        // See https://symfony.com/doc/current/console/style.html
        $this->io = new SymfonyStyle($input, $output);
    }
    protected function interact(InputInterface $input, OutputInterface $output): void
    {
        if (null !== $input->getArgument('email') && null !== $input->getArgument('password') && null !== $input->getArgument('role')) {
            return;
        }

        $this->io->title('Add User Command Wizard');
        $this->io->text([
            'If you prefer to not use this interactive wizard, provide the',
            'arguments required by this command as follows:',
            '',
            ' $ php bin/console app:add-user username password email@example.com',
            '',
            'Now we\'ll ask you for the value of all the missing command arguments.',
        ]);

        // Ask for the password if it's not defined
        /** @var string|null $password */
        $password = $input->getArgument('password');
        if (null !== $password) {
            $this->io->text(' > <info>Password</info>: '.u('*')->repeat(u($password)->length()));
        } else {
            $password = $this->io->askHidden('Password (your type will be hidden)', [$this->validator, 'validatePassword']);
            $input->setArgument('password', $password);
        }

        // Ask for the email if it's not defined
        $email = $input->getArgument('email');
        if (null !== $email) {
            $this->io->text(' > <info>Email</info>: '.$email);
        } else {
            $email = $this->io->ask('Email', null, [$this->validator, 'validateEmail']);
            $input->setArgument('email', $email);
        }
        $role = $input->getArgument('role');
        if (null !== $role) {
            $this->io->text(' > <info>Role</info>: '.u($role)->trim()->upper());
        } else {
            $role = $this->io->ask('Role (ROLE_STUDENT or ROLE_TEACHER)', null, [$this->validator, 'validateRoles']);
            $input->setArgument('role', u($role)->trim()->upper());
        }

    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $stopwatch = new Stopwatch();
        $stopwatch->start('add-user-command');

        /** @var string $plainPassword */
        $plainPassword = $input->getArgument('password');

        /** @var string $email */
        $email = $input->getArgument('email');

        $role = strtoupper(trim($input->getArgument('role')));

        // make sure to validate the user data is correct
        $this->validateUserData($plainPassword, $email, $role);

        // create the user and hash its password
        $user = new User();
        $user->setEmail($email);
        $user->setRoles([$role]);

        // See https://symfony.com/doc/5.4/security.html#registering-the-user-hashing-passwords
        $hashedPassword = $this->passwordHasher->hashPassword($user, $plainPassword);
        $user->setPassword($hashedPassword);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $this->io->success(sprintf('%s was successfully created: %s', 'User',$user->getEmail()));

        $event = $stopwatch->stop('add-user-command');
        if ($output->isVerbose()) {
            $this->io->comment(sprintf('New user database id: %d / Elapsed time: %.2f ms / Consumed memory: %.2f MB', $user->getId(), $event->getDuration(), $event->getMemory() / (1024 ** 2)));

        }
        return Command::SUCCESS;
    }
    private function validateUserData(string $plainPassword, string $email,string $role): void
    {
        // validate password and email if is not this input means interactive.
        $this->validator->validatePassword($plainPassword);
        $this->validator->validateEmail($email);
        $this->validator->validateRoles($role);

        // check if a user with the same email already exists.
        $existingEmail = $this->users->findOneBy(['email' => $email]);

        if (null !== $existingEmail) {
            throw new RuntimeException(sprintf('There is already a user registered with the "%s" email.', $email));
        }
    }
    private function getCommandHelp(): string
    {
        return <<<'HELP'
            The <info>%command.name%</info> command creates new users and saves them in the database:
              <info>php %command.full_name%</info> <comment>username password email</comment>
            By default the command creates regular users. To create administrator users,
            add the <comment>--admin</comment> option:
              <info>php %command.full_name%</info> username password email <comment>--admin</comment>
            If you omit any of the three required arguments, the command will ask you to
            provide the missing values:
              # command will ask you for the email
              <info>php %command.full_name%</info> <comment>username password</comment>
              # command will ask you for the email and password
              <info>php %command.full_name%</info> <comment>username</comment>
              # command will ask you for all arguments
              <info>php %command.full_name%</info>
            HELP;
    }


}