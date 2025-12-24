<?php

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/db.php';
function dummy_books(): array {
  return [
    ['id'=>1,'isbn'=>'9780131103627','title'=>'C Programming Language','category'=>'Science','year'=>1988,'price'=>450,'publisher'=>'Pearson','authors'=>['Kernighan','Ritchie'],'stock'=>8],
    ['id'=>2,'isbn'=>'9780262033848','title'=>'Introduction to Algorithms','category'=>'Science','year'=>2009,'price'=>750,'publisher'=>'MIT Press','authors'=>['Cormen','Leiserson','Rivest','Stein'],'stock'=>2],
    ['id'=>3,'isbn'=>'9780140449136','title'=>'The Odyssey','category'=>'History','year'=>2003,'price'=>240,'publisher'=>'Penguin','authors'=>['Homer'],'stock'=>0],
    ['id'=>4,'isbn'=>'9780061120084','title'=>'To Kill a Mockingbird','category'=>'Art','year'=>2006,'price'=>320,'publisher'=>'Harper','authors'=>['Harper Lee'],'stock'=>14],
    ['id'=>5,'isbn'=>'9780141439518','title'=>'Pride and Prejudice','category'=>'Art','year'=>2003,'price'=>280,'publisher'=>'Penguin','authors'=>['Jane Austen'],'stock'=>1],
    ['id'=>6,'isbn'=>'9780199535569','title'=>'A Brief History of Time','category'=>'Geography','year'=>2011,'price'=>390,'publisher'=>'Oxford','authors'=>['Stephen Hawking'],'stock'=>6],
  ];
}

function find_book_by_id(int $id): ?array {
  foreach (dummy_books() as $b) if ($b['id'] === $id) return $b;
  return null;
}
