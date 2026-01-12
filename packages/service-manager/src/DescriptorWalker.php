<?php

declare(strict_types=1);

namespace IfCastle\ServiceManager;

use IfCastle\TypeDefinitions\FunctionDescriptorInterface;

final class DescriptorWalker
{
    /**
     * @return iterable<string, FunctionDescriptorInterface>
     */
    public static function walk(DescriptorRepositoryInterface $descriptorRepository): iterable
    {
        foreach ($descriptorRepository->getServiceDescriptorList() as $serviceDescriptor) {
            foreach ($serviceDescriptor->getServiceMethods() as $serviceMethod) {
                $isBreak = yield $serviceDescriptor->getServiceName() => $serviceMethod;

                if (true === $isBreak) {
                    return;
                }
            }
        }
    }

    /**
     * @return iterable<string, array{0: ServiceDescriptorInterface, 1: FunctionDescriptorInterface}>
     */
    public static function walkWithService(DescriptorRepositoryInterface $descriptorRepository): iterable
    {
        foreach ($descriptorRepository->getServiceDescriptorList() as $serviceDescriptor) {
            foreach ($serviceDescriptor->getServiceMethods() as $serviceMethod) {
                $isBreak = yield $serviceDescriptor->getServiceName() => [$serviceDescriptor, $serviceMethod];

                if (true === $isBreak) {
                    return;
                }
            }
        }
    }
}
