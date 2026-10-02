<?php

namespace Database\Seeders;

use App\Enums\ProducerStatus;
use App\Enums\ProducerType;
use App\Enums\UserRole;
use App\Models\Category;
use App\Models\City;
use App\Models\Producer;
use App\Models\User;
use Database\Seeders\Support\DemoImage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Производители (с аккаунтами), торговые точки, товары с вариантами и фото,
 * а также одна заявка производителя, ожидающая проверки администратором.
 */
class ProducerSeeder extends Seeder
{
    public function run(): void
    {
        $subcategories = Category::whereNotNull('parent_id')->get()->keyBy('name');

        foreach ($this->producers() as $index => $data) {
            $city = City::where('name', $data['city'])->first();

            $user = User::create([
                'name' => $data['owner'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'city_id' => $city->id,
                'role' => UserRole::Producer,
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]);

            $producer = Producer::create([
                'user_id' => $user->id,
                'city_id' => $city->id,
                'name' => $data['name'],
                'type' => $data['type'],
                'inn' => $data['inn'],
                'description' => $data['description'],
                'phone' => $data['phone'],
                'email' => $data['email'],
                'address' => $data['address'],
                'status' => ProducerStatus::Approved,
                'approved_at' => now()->subMonths(3)->addDays($index * 5),
            ]);

            $producer->update([
                'logo' => DemoImage::logo("producers/{$producer->slug}.svg", $data['logo'][0], $data['logo'][1]),
            ]);
            $producer->forceFill(['created_at' => now()->subMonths(3)->addDays($index * 5)])->save();

            $producer->applications()->forceCreate([
                'user_id' => $user->id,
                'message' => 'Хотим продавать нашу продукцию на платформе.',
                'status' => ProducerStatus::Approved,
                'reviewed_at' => $producer->approved_at,
                'created_at' => $producer->approved_at->copy()->subDays(2),
            ]);

            foreach ($data['locations'] as [$name, $address, $hours, $pickup]) {
                $producer->locations()->create([
                    'name' => $name,
                    'address' => $address,
                    'working_hours' => $hours,
                    'phone' => $data['phone'],
                    'is_pickup_point' => $pickup,
                ]);
            }

            foreach ($data['products'] as $productIndex => $item) {
                $this->createProduct($producer, $subcategories, $item, $productIndex);
            }
        }

        $this->createPendingApplication();
    }

    private function createProduct(Producer $producer, $subcategories, array $item, int $index): void
    {
        $subcategory = $subcategories[$item['sub']];

        $product = $producer->products()->create([
            'category_id' => $subcategory->parent_id,
            'subcategory_id' => $subcategory->id,
            'name' => $item['name'],
            'description' => $item['description'],
            'price' => $item['price'] ?? 0,
            'unit' => $item['unit'] ?? null,
            'in_stock' => $item['in_stock'] ?? true,
            'is_active' => true,
        ]);

        $product->update([
            'image' => DemoImage::product("products/{$product->slug}.svg", $item['emoji'], $item['bg']),
        ]);

        // Дополнительные фотографии для галереи
        for ($i = 1; $i <= ($item['extra'] ?? 0); $i++) {
            $product->images()->create([
                'path' => DemoImage::product("products/{$product->slug}-{$i}.svg", $item['emoji'], $item['bg'], $i),
                'sort_order' => $i,
            ]);
        }

        foreach ($item['variants'] ?? [] as $sort => $variant) {
            $product->variants()->create([
                'name' => $variant[0],
                'price' => $variant[1],
                'in_stock' => $variant[2] ?? true,
                'sort_order' => $sort,
            ]);
        }

        if (! empty($item['variants'])) {
            $product->syncPriceFromVariants();
        }

        // Разные даты добавления — для сортировки «Новые»
        $product->forceFill(['created_at' => now()->subDays(60 - $index * 6 - $producer->id)])->saveQuietly();
    }

    /** Покупатель Игорь подал заявку на регистрацию пасеки — её нужно проверить в админке. */
    private function createPendingApplication(): void
    {
        $user = User::where('email', 'igor@localmarket.test')->first();
        $city = City::where('name', 'Казань')->first();

        $producer = Producer::create([
            'user_id' => $user->id,
            'city_id' => $city->id,
            'name' => 'Пасека «Медовый край»',
            'type' => ProducerType::Individual,
            'inn' => '165012345678',
            'description' => 'Семейная пасека в Высокогорском районе. Липовый, гречишный и разнотравный мёд, перга, прополис.',
            'phone' => $user->phone,
            'email' => 'honey@localmarket.test',
            'address' => 'Высокогорский район, с. Чепчуги, ул. Садовая, 4',
            'status' => ProducerStatus::Pending,
        ]);

        $producer->applications()->forceCreate([
            'user_id' => $user->id,
            'message' => 'Здравствуйте! Мы небольшая семейная пасека, 60 ульев. Хотим продавать мёд жителям Казани с доставкой.',
            'status' => ProducerStatus::Pending,
            'created_at' => now()->subDay(),
        ]);
    }

    private function producers(): array
    {
        return [
            [
                'name' => 'Ферма «Зелёный луг»',
                'owner' => 'Николай Фермеров',
                'email' => 'farm@localmarket.test',
                'phone' => '+7 (917) 900-10-10',
                'type' => ProducerType::Individual,
                'inn' => '162100123456',
                'city' => 'Казань',
                'address' => 'Пестречинский район, д. Ленино, ул. Полевая, 12',
                'logo' => ['🐄', '#2f7a55'],
                'description' => "Небольшая семейная ферма в 30 км от Казани. Держим 40 коров на вольном выпасе, сами делаем сыры, творог и масло.\nВся продукция — без консервантов и сухого молока.",
                'locations' => [
                    ['Лавка на Центральном рынке', 'г. Казань, ул. Московская, 6, павильон 14', 'Ежедневно 8:00–19:00', true],
                    ['Ферма (самовывоз)', 'Пестречинский район, д. Ленино, ул. Полевая, 12', 'Сб–Вс 10:00–16:00', true],
                ],
                'products' => [
                    ['name' => 'Молоко фермерское 3,5–4,5%', 'sub' => 'Молочные продукты', 'price' => 120, 'unit' => '1 л', 'emoji' => '🥛', 'bg' => 'blue', 'extra' => 1,
                        'description' => "Цельное молоко утреннего надоя. Не нормализованное, жирность зависит от сезона.\nХранить при +2…+4 °C не более 4 суток."],
                    ['name' => 'Сыр «Качотта» выдержанный', 'sub' => 'Молочные продукты', 'unit' => 'кусок', 'emoji' => '🧀', 'bg' => 'cream', 'extra' => 2,
                        'variants' => [['250 г', 390], ['500 г', 750], ['1 кг', 1450]],
                        'description' => 'Полутвёрдый итальянский сыр из коровьего молока, выдержка 60 дней. Нежный сливочный вкус с лёгкой кислинкой.'],
                    ['name' => 'Творог домашний 9%', 'sub' => 'Молочные продукты', 'price' => 260, 'unit' => '500 г', 'emoji' => '🥣', 'bg' => 'cream',
                        'description' => 'Рассыпчатый творог из цельного молока на натуральной закваске.'],
                    ['name' => 'Сметана 25%', 'sub' => 'Молочные продукты', 'price' => 190, 'unit' => '400 г', 'emoji' => '🍶', 'bg' => 'blue',
                        'description' => 'Густая сметана из сливок, сквашенных живой закваской.'],
                    ['name' => 'Масло сливочное 82,5%', 'sub' => 'Молочные продукты', 'price' => 320, 'unit' => '200 г', 'emoji' => '🧈', 'bg' => 'sand',
                        'description' => 'Сладкосливочное масло ручной сбивки. Только сливки — ничего лишнего.'],
                    ['name' => 'Яйца куриные деревенские', 'sub' => 'Мясо, птица и яйца', 'unit' => 'упаковка', 'emoji' => '🥚', 'bg' => 'peach',
                        'variants' => [['10 шт', 150], ['20 шт', 280]],
                        'description' => 'Яйца от кур свободного выгула. Яркий желток, крепкая скорлупа.'],
                    ['name' => 'Мёд липовый', 'sub' => 'Мёд и сладости', 'unit' => 'банка', 'emoji' => '🍯', 'bg' => 'sand', 'extra' => 1,
                        'variants' => [['0,5 кг', 550], ['1 кг', 990]],
                        'description' => 'Светлый липовый мёд с соседней пасеки. Урожай этого года, не нагревался.'],
                    ['name' => 'Морковь мытая', 'sub' => 'Овощи и зелень', 'price' => 70, 'unit' => '1 кг', 'emoji' => '🥕', 'bg' => 'peach', 'in_stock' => false,
                        'description' => 'Сладкая морковь с собственного огорода. Новый урожай ожидается в июле.'],
                ],
            ],
            [
                'name' => 'Пекарня «Тёплый хлеб»',
                'owner' => 'Марина Хлебникова',
                'email' => 'bakery@localmarket.test',
                'phone' => '+7 (917) 900-20-20',
                'type' => ProducerType::Organization,
                'inn' => '1655123456',
                'city' => 'Казань',
                'address' => 'г. Казань, ул. Кремлёвская, 21',
                'logo' => ['🥖', '#b5713f'],
                'description' => 'Ремесленная пекарня в центре Казани. Печём хлеб на живой закваске и французскую выпечку каждое утро с 5:00.',
                'locations' => [
                    ['Пекарня на Кремлёвской', 'г. Казань, ул. Кремлёвская, 21', 'Ежедневно 7:30–21:00', true],
                    ['Кофейня-пекарня на Баумана', 'г. Казань, ул. Баумана, 44', 'Ежедневно 8:00–22:00', true],
                    ['Производственный цех', 'г. Казань, ул. Техническая, 10', 'Не для посетителей', false],
                ],
                'products' => [
                    ['name' => 'Хлеб на закваске «Деревенский»', 'sub' => 'Хлеб', 'price' => 180, 'unit' => 'буханка 700 г', 'emoji' => '🍞', 'bg' => 'sand', 'extra' => 1,
                        'description' => 'Пшенично-ржаной хлеб на живой закваске, ферментация 18 часов. Хрустящая корка и влажный мякиш.'],
                    ['name' => 'Багет французский', 'sub' => 'Хлеб', 'price' => 110, 'unit' => 'шт', 'emoji' => '🥖', 'bg' => 'cream',
                        'description' => 'Классический багет с тонкой хрустящей корочкой. Выпекается несколько раз в день.'],
                    ['name' => 'Чиабатта с оливками', 'sub' => 'Хлеб', 'price' => 140, 'unit' => 'шт', 'emoji' => '🫓', 'bg' => 'green',
                        'description' => 'Итальянский хлеб с крупными порами, оливками каламата и оливковым маслом.'],
                    ['name' => 'Круассан классический', 'sub' => 'Сладкая выпечка', 'unit' => 'шт', 'emoji' => '🥐', 'bg' => 'peach', 'extra' => 2,
                        'variants' => [['1 шт', 95], ['Набор 4 шт', 340], ['Набор 8 шт', 640]],
                        'description' => 'Слоёный круассан на французском сливочном масле 82,5%. 27 слоёв теста.'],
                    ['name' => 'Синнабон с корицей', 'sub' => 'Сладкая выпечка', 'price' => 150, 'unit' => 'шт', 'emoji' => '🥯', 'bg' => 'sand',
                        'description' => 'Мягкая булочка с корицей и нежным сливочным кремом.'],
                    ['name' => 'Пирог с вишней', 'sub' => 'Пироги', 'unit' => 'шт', 'emoji' => '🥧', 'bg' => 'pink',
                        'variants' => [['Кусок 150 г', 180], ['Целый, 1,2 кг', 990]],
                        'description' => 'Открытый пирог на песочном тесте с вишней и миндальным кремом.'],
                ],
            ],
            [
                'name' => 'Обжарочная «Зерно»',
                'owner' => 'Артём Обжаркин',
                'email' => 'coffee@localmarket.test',
                'phone' => '+7 (917) 900-30-30',
                'type' => ProducerType::SelfEmployed,
                'inn' => '165700112233',
                'city' => 'Казань',
                'address' => 'г. Казань, ул. Островского, 38',
                'logo' => ['☕', '#4a3a2e'],
                'description' => 'Обжариваем specialty-кофе небольшими партиями раз в неделю. Помогаем подобрать помол под ваш способ заваривания.',
                'locations' => [
                    ['Обжарочная и зерновой бар', 'г. Казань, ул. Островского, 38', 'Пн–Сб 9:00–20:00', true],
                ],
                'products' => [
                    ['name' => 'Кофе «Эфиопия Иргачеффе»', 'sub' => 'Кофе', 'unit' => 'пачка', 'emoji' => '☕', 'bg' => 'sand', 'extra' => 1,
                        'variants' => [['250 г', 690], ['500 г', 1290], ['1 кг', 2390]],
                        'description' => 'Мытая обработка, светлая обжарка. Ноты бергамота, жасмина и лимона. Подходит для фильтра и эспрессо.'],
                    ['name' => 'Кофе «Бразилия Сантос»', 'sub' => 'Кофе', 'unit' => 'пачка', 'emoji' => '🫘', 'bg' => 'cream',
                        'variants' => [['250 г', 590], ['500 г', 1090], ['1 кг', 1990, false]],
                        'description' => 'Классический бразильский кофе средней обжарки: орех, молочный шоколад, карамель.'],
                    ['name' => 'Дрип-пакеты, набор 10 шт', 'sub' => 'Кофе', 'price' => 450, 'unit' => 'набор', 'emoji' => '🎁', 'bg' => 'peach',
                        'description' => 'Молотый кофе в фильтр-пакетах: просто залейте горячей водой. Удобно в офис и в поездку.'],
                    ['name' => 'Иван-чай с чабрецом', 'sub' => 'Чай', 'price' => 290, 'unit' => '100 г', 'emoji' => '🍵', 'bg' => 'green',
                        'description' => 'Ферментированный иван-чай ручного сбора с добавлением чабреца.'],
                    ['name' => 'Лимонад «Облепиха и имбирь»', 'sub' => 'Соки и лимонады', 'price' => 180, 'unit' => '0,5 л', 'emoji' => '🍹', 'bg' => 'peach',
                        'description' => 'Освежающий лимонад из облепихи с имбирём и мёдом. Без консервантов.'],
                ],
            ],
            [
                'name' => 'Цветочная студия «Пион»',
                'owner' => 'Дарья Цветкова',
                'email' => 'flowers@localmarket.test',
                'phone' => '+7 (917) 900-40-40',
                'type' => ProducerType::Individual,
                'inn' => '165900445566',
                'city' => 'Казань',
                'address' => 'г. Казань, ул. Петербургская, 9',
                'logo' => ['🌷', '#c25b78'],
                'description' => 'Собираем авторские букеты из сезонных цветов от местных тепличных хозяйств. Доставим к нужному часу.',
                'locations' => [
                    ['Цветочная студия', 'г. Казань, ул. Петербургская, 9', 'Ежедневно 9:00–21:00', true],
                ],
                'products' => [
                    ['name' => 'Букет «Летнее утро»', 'sub' => 'Букеты', 'unit' => 'букет', 'emoji' => '💐', 'bg' => 'pink', 'extra' => 2,
                        'variants' => [['S', 1900], ['M', 2900], ['L', 3900]],
                        'description' => 'Нежный букет из кустовых роз, эустомы и эвкалипта в крафтовой упаковке.'],
                    ['name' => 'Монобукет из 15 тюльпанов', 'sub' => 'Букеты', 'price' => 2400, 'unit' => 'букет', 'emoji' => '🌷', 'bg' => 'lilac',
                        'description' => 'Свежие тюльпаны из тепличного хозяйства. Цвет уточним при заказе.'],
                    ['name' => 'Суккулент в керамическом кашпо', 'sub' => 'Комнатные растения', 'price' => 890, 'unit' => 'шт', 'emoji' => '🪴', 'bg' => 'mint',
                        'description' => 'Неприхотливое растение в кашпо ручной работы. Отличный подарок.'],
                    ['name' => 'Монстера в горшке', 'sub' => 'Комнатные растения', 'price' => 2600, 'unit' => 'горшок d17 см', 'emoji' => '🌿', 'bg' => 'green',
                        'description' => 'Монстера деликатесная высотой 60–70 см. Растёт быстро, любит рассеянный свет.'],
                ],
            ],
            [
                'name' => 'Мастерская «Глина и лён»',
                'owner' => 'Вера Гончарова',
                'email' => 'craft@localmarket.test',
                'phone' => '+7 (920) 900-50-50',
                'type' => ProducerType::SelfEmployed,
                'inn' => '526300778899',
                'city' => 'Нижний Новгород',
                'address' => 'г. Нижний Новгород, ул. Рождественская, 24',
                'logo' => ['🏺', '#8a6d5a'],
                'description' => 'Небольшая мастерская керамики и текстиля. Каждое изделие создаётся вручную и существует в единственном экземпляре.',
                'locations' => [
                    ['Мастерская', 'г. Нижний Новгород, ул. Рождественская, 24', 'Вт–Вс 11:00–19:00', true],
                ],
                'products' => [
                    ['name' => 'Кружка ручной работы «Лес»', 'sub' => 'Керамика', 'price' => 1200, 'unit' => '350 мл', 'emoji' => '☕', 'bg' => 'mint', 'extra' => 1,
                        'description' => 'Кружка из шамотной глины, покрыта глазурью цвета хвои. Можно мыть в посудомоечной машине.'],
                    ['name' => 'Тарелка глиняная', 'sub' => 'Керамика', 'price' => 1500, 'unit' => 'd 24 см', 'emoji' => '🍽️', 'bg' => 'sand',
                        'description' => 'Обеденная тарелка из красной глины с матовой белой глазурью.'],
                    ['name' => 'Свеча соевая «Хвоя»', 'sub' => 'Свечи и декор', 'unit' => 'баночка', 'emoji' => '🕯️', 'bg' => 'cream',
                        'variants' => [['100 мл', 650], ['200 мл', 990]],
                        'description' => 'Свеча из соевого воска с деревянным фитилём. Аромат хвои и кедра, горит до 40 часов.'],
                    ['name' => 'Льняная сумка-шопер', 'sub' => 'Аксессуары', 'price' => 1400, 'unit' => 'шт', 'emoji' => '👜', 'bg' => 'sand',
                        'description' => 'Прочная сумка из 100% льна с внутренним карманом. Ручная вышивка.'],
                    ['name' => 'Мыло ручной работы «Лаванда»', 'sub' => 'Мыло', 'price' => 350, 'unit' => '100 г', 'emoji' => '🧼', 'bg' => 'lilac',
                        'description' => 'Мыло холодного способа на оливковом и кокосовом маслах с эфирным маслом лаванды.'],
                    ['name' => 'Бальзам для губ «Облепиха»', 'sub' => 'Уход за телом', 'price' => 290, 'unit' => '10 мл', 'emoji' => '💄', 'bg' => 'peach',
                        'description' => 'Питательный бальзам на пчелином воске с маслом облепихи.'],
                ],
            ],
        ];
    }
}
