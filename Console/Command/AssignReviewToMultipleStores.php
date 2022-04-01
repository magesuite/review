<?php

namespace MageSuite\Review\Console\Command;

class AssignReviewToMultipleStores extends \Symfony\Component\Console\Command\Command
{
    protected \MageSuite\Review\Service\ReviewMultipleStoreAssignerFactory $multipleStoreAssignerFactory;

    public function __construct(\MageSuite\Review\Service\ReviewMultipleStoreAssignerFactory $multipleStoreAssignerFactory)
    {
        parent::__construct();
        $this->multipleStoreAssignerFactory = $multipleStoreAssignerFactory;
    }

    protected function configure()
    {
        $this->setName('magesuite:review:assign')
            ->setDescription('Assign reviews to multiple stores');
    }

    protected function execute(
        \Symfony\Component\Console\Input\InputInterface $input,
        \Symfony\Component\Console\Output\OutputInterface $output
    ) {
        $this->multipleStoreAssignerFactory->create()->execute();
    }
}
