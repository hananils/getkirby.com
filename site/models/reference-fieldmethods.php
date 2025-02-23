<?php

use Kirby\Cms\Pages;
use Kirby\Reference\SectionPage;
use Kirby\Toolkit\Str;

class ReferenceFieldMethodsPage extends SectionPage
{
	public function children(): Pages
	{
		if ($this->children !== null) {
			return $this->children;
		}

		$children = [];
		$pages    = parent::children();
		$methods  = array_merge(
			$this->getDynamicMethods(),
			$this->getNativeMethods()
		);

		foreach ($methods as $name => $reflection) {
			$children[] = [
				'slug'     => $slug = Str::kebab($name),
				'num'      => 0,
				'template' => 'reference-fieldmethod',
				'model'    => 'reference-fieldmethod',
				'parent'   => $this,
				'content'  => $pages->find($slug)?->content()->toArray() ?? []
			];
		}

		return $this->children = Pages::factory($children, $this)
			->filterBy('isInternal', false)
			->sortBy('title', 'asc');
	}

	protected function getDynamicMethods(): array
	{
		$methods = (include $this->kirby()->root('kirby') . '/config/methods.php')($this->kirby());

		foreach ($methods as $key => $function) {
			$methods[$key] = new ReflectionFunction($function);
		}

		return $methods;
	}

	protected function getNativeMethods(): array
	{
		$methods    = [];
		$reflection = new ReflectionClass(Field::class);

		foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
			$name = $method->getName();

			if (substr($name, 0, 1) !== '_') {
				$methods[$name] = $method;
			}
		}

		return $methods;
	}
}
