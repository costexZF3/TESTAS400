<?php
    namespace User\Controller;

    use Zend\Mvc\Controller\AbstractActionController;    
    use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator as DoctrineAdapter;
    use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;
    use Zend\Paginator\Paginator;
    use Zend\View\Model\ViewModel;
    use User\Entity\User;
    use User\Entity\UserAS400;
    use User\Entity\Role;
    //use User\Entity\Permission;
    use User\Form\UserForm;
    use User\Form\PasswordChangeForm;
    use User\Form\PasswordResetForm;

    /**
     * This controller is responsible for user management (adding, editing, 
     * viewing users and changing user's password).
     */
    class UserController extends AbstractActionController 
    {
        /**
         * Entity manager.
         * @var Doctrine\ORM\EntityManager
         */
        private $entityManager;

        /**
         * User manager.
         * @var User\Service\UserManager 
         */
        private $userManager;

        /**
         * Constructor. 
         */
        public function __construct($entityManager, $userManager)
        {
            $this->entityManager = $entityManager;
            $this->userManager = $userManager;
        }
        
        private function createButtonsOnLayout()
        {        
            $buttonADD = [
                'label' => 'new user',
                'title' => 'add user',
                'class' => 'boxed-btn-layout btn-rounded',
                'font-icon' => 'fa fa-user-plus fa-1x',
                'url' => [                          
                    'route'=>'users', 
                    'action'=>['action'=>'add'],                            
                ],
            ];

            $buttonList = [];
            array_push($buttonList, $buttonADD);
            
            return $buttonList;
        }
        
        
        /**
         * This is the default "index" action of the controller. It displays the 
         * list of users.
         */
        public function indexAction() 
        {
            $this->layout()->setTemplate('layout/layout_Grid');
            // Access control.
            if (!$this->access('manage.user')) {
                $this->getResponse()->setStatusCode(401);
                return;
            }
            
            //creating buttons will be rendering on the breadcrums (new item, import from excel)
            $this->layout()->buttons = $this->createButtonsOnLayout();
            
            $users = $this->entityManager->getRepository(User::class)
                         ->findBy([], ['id'=>'ASC']);
            
            return new ViewModel([
                'users' => $users 
            ]);
        } 

        /**
         * This action displays a page allowing to add a new user.
         */
        public function addAction()
        {
            // Create user form
            $form = new UserForm('create', $this->entityManager);
            // Get the list of all available roles (sorted by name).
            $allRoles = $this->entityManager->getRepository(Role::class)
                    ->findBy([], ['name'=>'ASC']);
            $roleList = [];
            foreach ($allRoles as $role) {
                $roleList[$role->getId()] = $role->getName();
            }
            //INSERTING ALL ROLES INTO THE roles FORM ITEM, TO SELECCTING
            $form->get('roles')->setValueOptions($roleList);

            // Check if user has submitted the form
            if ($this->getRequest()->isPost()) {
                // Fill in the form with POST data
                $data = $this->params()->fromPost(); 
                $form->setData($data);

                // Validate form
                if($form->isValid()) {
                    // Get filtered and validated data
                    $data = $form->getData();
                    // Add user.
                    $user = $this->userManager->addUser($data);

                    // Redirect to "view" page
                    return $this->redirect()->toRoute('users', 
                            ['action'=>'view', 'id'=>$user->getId()]);                
                }               
            } 

            return new ViewModel([
                    'form' => $form
                ]);
        }

        /**
         * The "viewAction" displays a page allowing to view user's details.
         */
        public function viewAction() 
        {
            $id = (int)$this->params()->fromRoute('id', -1);
            if ($id<1) {
                $this->getResponse()->setStatusCode(404);
                return;
            }

            // Find a user with such ID.
            $user = $this->entityManager->getRepository(User::class)
                    ->find($id);

            if ($user == null) {
                $this->getResponse()->setStatusCode(404);
                return;
            }

            return new ViewModel([
                'user' => $user
            ]);
        }

        /**
         * The "edit" action displays a page allowing to edit user.
         */
        public function editAction() 
        {
            $id = (int)$this->params()->fromRoute('id', -1);
            if ($id<1) {
                $this->getResponse()->setStatusCode(404);
                return;
            }

            $user = $this->entityManager->getRepository(User::class)
                    ->find($id);

            if ($user == null) {
                $this->getResponse()->setStatusCode(404);
                return;
            }

            // Create user form
            $form = new UserForm('update', $this->entityManager, $user);

            // Get the list of all available roles (sorted by name).
            $allRoles = $this->entityManager->getRepository(Role::class)
                    ->findBy([], ['name'=>'ASC']);
            
            $roleList = [];
            foreach ($allRoles as $role) {
                $roleList[$role->getId()] = $role->getName();
            }
            
            if (count($roleList) > 9) {            
                $form->get('roles')->setAttributes([
                    'class'=>'form-control', 
                    'size' => 12,
                ]);
            }
            /**
             * HTML: SELECT ITEM IS FILLED WITH ALL ROLES 
             * getting the instance from the role and setting the $roleList to the form Item : roles 
             */
            $form->get('roles')->setValueOptions($roleList);

            // Check if user has submitted the form
            if ($this->getRequest()->isPost()) {

                // Fill in the form with POST data
                $data = $this->params()->fromPost();            

                $form->setData($data);

                // Validate form
                if($form->isValid()) {

                    // Get filtered and validated data
                    $data = $form->getData();

                    // Update the user.
                    $this->userManager->updateUser($user, $data);

                    // Redirect to "view" page
                    return $this->redirect()->toRoute('users', 
                            ['action'=>'view', 'id'=>$user->getId()]);                
                }               
            } else {

                $userRoleIds = [];
                foreach ($user->getRoles() as $role) {
                    $userRoleIds[] = $role->getId();
                }

                $form->setData( array(
                        'full_name' => $user->getFullName(),
                            'email' => $user->getEmail(),
                            'status'=>$user->getStatus(), 
                            'roles' => $userRoleIds
                    ));
            } //end: else

            return new ViewModel( array(
                'user' => $user,
                'form' => $form
            ));
        }// end method: editAction() 

        /**
         * This action displays a page allowing to change user's password.
         */
        public function changePasswordAction() {
            $id = (int)$this->params()->fromRoute('id', -1);
            if ($id<1) {
                $this->getResponse()->setStatusCode(404);
                return;
            }

            $user = $this->entityManager->getRepository(User::class)
                    ->find($id);

            if ($user == null) {
                $this->getResponse()->setStatusCode( 404 );
                return;
            }

            // checking access to +user.manage permission for the user logged in   
            $isUserManager = $this->access('manage.user');
            
            $passwordChangeOrReset = ( $isUserManager )?"reset":"change";
            
            // Create "change password" form
            $form = new PasswordChangeForm( $passwordChangeOrReset );
                       
            // Check if user has submitted the form
            if ($this->getRequest()->isPost()) {

                // Fill in the form with POST data
                $data = $this->params()->fromPost();            

                $form->setData( $data );

                // Validate form
                if($form->isValid()) {
                    // Get filtered and validated data
                    $data = $form->getData();
                    
                    // Try to change password.
                    if (!$this->userManager->changePassword($user, $data, $passwordChangeOrReset)) {
                        $this->flashMessenger()->addErrorMessage(
                                'Sorry, the old password is incorrect. Could not set the new password.');
                    } else {
                        $this->flashMessenger()->addSuccessMessage(
                                'Changed the password successfully.');
                    }

                    // Redirect to "view" page
                    return $this->redirect()->toRoute('users', 
                            ['action'=>'view', 'id'=>$user->getId()]);                
                }               
            } 
            
            return new ViewModel([
                'user' => $user,
                'form' => $form,
     'showoldpassword' => $passwordChangeOrReset,
            ]);
        }

        /**
         * This action displays the "Reset Password" page.
         */
        public function resetPasswordAction()
        {
            // Create form
            $form = new PasswordResetForm();

            // Check if user has submitted the form
            if ($this->getRequest()->isPost()) {

                // Fill in the form with POST data
                $data = $this->params()->fromPost();            

                $form->setData($data);

                // Validate form
                if($form->isValid()) {

                    // Look for the user with such email.
                    $user = $this->entityManager->getRepository(User::class)
                            ->findOneByEmail($data['email']);                
                    if ($user!=null) {
                        // Generate a new password for user and send an E-mail 
                        // notification about that.
                        $this->userManager->generatePasswordResetToken($user);
                            
                        // Redirect to "message" page WITH id='sent' 
                        return $this->redirect()->toRoute('users', 
                                ['action'=>'message', 'id'=>'sent']);                 
                    } else {
                        return $this->redirect()->toRoute('users', 
                                ['action'=>'message', 'id'=>'invalid-email']);                 
                    }
                }               
            }//END IF: checking submitted data 
            
            //create a ViewModel with all $form data that will used in the View reset-password.phml
            return new ViewModel([                    
                'form' => $form
            ]);
        }

        /**
         * This action displays an informational message page. 
         * For example "Your password has been resetted" and so on.
         */
        public function messageAction() 
        {
            // Get message ID from route.
            $id = (string)$this->params()->fromRoute('id');

            // Validate input argument.
            if($id!='invalid-email' && $id!='sent' && $id!='set' && $id!='failed') {
                throw new \Exception('Invalid message ID specified');
            }

            return new ViewModel([
                'id' => $id
            ]);
        }

        /**
         * This action displays the "Reset Password" page. 
         */
        public function setPasswordAction()
        {
            $token = $this->params()->fromQuery('token', null);
            $email = $this->params()->fromQuery('email', null);

            // Validate token length
            if ($token===null) {
                throw new \Exception('Invalid token type or length');
            }            
            
            //*****  TESTING *****            
            if($token===null || 
               !$this->userManager->validatePasswordResetToken($email,$token)) {
                return $this->redirect()->toRoute('users', 
                        ['action'=>'message', 'id'=>'failed']);
            }

            // Create form
            $form = new PasswordChangeForm('reset');

            // Check if user has submitted the form
            if ($this->getRequest()->isPost()) {

                // Fill in the form with POST data
                $data = $this->params()->fromPost();            

                $form->setData($data);

                // Validate form
                if($form->isValid()) {

                    $data = $form->getData();

                    // Set new password for the user.
                    if ($this->userManager->setNewPasswordByToken($email, $token, $data['new_password'])) {

                        // Redirect to "message" page
                        return $this->redirect()->toRoute('users', 
                                                              ['action'=>'message', 'id'=>'set']);                 
                    } else {
                        // Redirect to "message" page
                        return $this->redirect()->toRoute('users', 
                                ['action'=>'message', 'id'=>'failed']);                 
                    }
                }               
            } 

            return new ViewModel([                    
                'form' => $form
            ]);
        }
    }//END: class UserController


