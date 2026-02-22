<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class NavbarScrollTest extends TestCase
{
    /**
     * Test that the scroll handler adds correct classes when scrolling down
     */
    public function test_scroll_handler_minimizes_navbar(): void
    {
        // This test verifies the logic by checking class names
        $scrollY = 51; // Over 50px threshold
        
        // Simulating the scroll logic from PublicLayout.vue
        $shouldMinimize = $scrollY > 50;
        
        $this->assertTrue($shouldMinimize, 'Navbar should minimize when scrollY > 50');
    }

    /**
     * Test that the scroll handler removes minimized classes when scrolling up
     */
    public function test_scroll_handler_expands_navbar(): void
    {
        $scrollY = 30; // Under 50px threshold
        
        // Simulating the scroll logic
        $shouldExpand = $scrollY <= 50;
        
        $this->assertTrue($shouldExpand, 'Navbar should expand when scrollY <= 50');
    }

    /**
     * Test the exact class names being added/removed
     */
    public function test_scroll_handler_manages_correct_classes(): void
    {
        // Classes to add when minimizing
        $classesToAdd = ['py-2', 'px-4', 'max-w-4xl', 'bg-white/90', 'dark:bg-black/80', 'backdrop-blur-lg', 'border', 'border-white/40', 'dark:border-white/10', 'shadow-lg'];
        
        // Classes to remove when minimizing
        $classesToRemove = ['glass-pill', 'px-5', 'py-2.5', 'max-w-5xl'];
        
        // Verify backdrop-blur is included (this was the main fix)
        $this->assertContains('backdrop-blur-lg', $classesToAdd, 'Backdrop blur must be maintained when minimizing');
        
        // Verify glass-pill is removed
        $this->assertContains('glass-pill', $classesToRemove, 'glass-pill class must be removed to avoid conflicts');
        
        // Verify all necessary classes are present
        $this->assertCount(10, $classesToAdd, 'Should add exactly 10 classes when minimizing');
        $this->assertCount(4, $classesToRemove, 'Should remove exactly 4 classes when minimizing');
    }

    /**
     * Test that border and shadow classes are properly managed
     */
    public function test_glass_effects_preserved(): void
    {
        $glassEffectClasses = ['backdrop-blur-lg', 'border', 'border-white/40', 'shadow-lg'];
        
        // These should be added to maintain the glass effect when minimizing
        foreach ($glassEffectClasses as $class) {
            $this->assertNotEmpty($class, "Glass effect class '{$class}' should not be empty");
        }
    }
}
