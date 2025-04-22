const stories = [
    {
        id: 29,
        title: "Sleeping Beauty",
        cover: "images/library/covers/sleeping-beauty.jpg",
        age_group: "7-9",
        reading_level: "intermediate",
        genre: "fairy tale",
        description: "A princess cursed to sleep for a hundred years until true love's kiss!",
        content: "stories/sleeping-beauty.html"
    },
    {
        id: 3,
        title: "The Crystal Dragon's Secret",
        cover: "images/library/covers/dragon-kingdom.avif",
        age_group: "9-11",
        reading_level: "advanced",
        genre: "fantasy",
        description: "Discover the secrets of the ancient dragon kingdom where magical creatures soar through crystal caves!",
        content: "stories/dragon-kingdom.html"
    },
    {
        id: 5,
        title: "Jungle Explorer's Diary",
        cover: "images/library/covers/jungle-animals.jpg",
        age_group: "7-9",
        reading_level: "intermediate",
        genre: "animals",
        description: "Explore the wild jungle and learn about amazing animals, their habitats, and survival skills!",
        content: "stories/jungle-animals.html"
    },
    {
        id: 16,
        title: "Captain Jack's Treasure Hunt",
        cover: "images/library/covers/pirate-treasure.avif",
        age_group: "7-9",
        reading_level: "intermediate",
        genre: "adventure",
        description: "Set sail with Captain Jack and his crew on a thrilling quest to find the legendary pirate treasure!",
        content: "stories/pirate-treasure.html"
    },
    {
        id: 12,
        title: "The Wizard's Apprentice",
        cover: "images/library/covers/wizard-school.jpeg",
        age_group: "9-11",
        reading_level: "advanced",
        genre: "fantasy",
        description: "Enter the magical world of Wizard School where young wizards learn spells and magical creatures abound!",
        content: "stories/wizard-school.html"
    },
    {
        id: 27,
        title: "Rapunzel",
        cover: "images/library/covers/rapunzel.jpg",
        age_group: "7-9",
        reading_level: "intermediate",
        genre: "fairy tale",
        description: "The story of a girl with magical long hair who discovers the world beyond her tower!",
        content: "stories/rapunzel.html"
    },
    {
        id: 4,
        title: "Coral's Ocean Adventure",
        cover: "images/library/covers/ocean-friends.avif",
        age_group: "5-7",
        reading_level: "beginner",
        genre: "animals",
        description: "Dive deep into the ocean and meet colorful sea creatures who teach valuable lessons about friendship!",
        content: "stories/ocean-friends.html"
    },
    {
        id: 28,
        title: "Beauty and the Beast",
        cover: "images/library/covers/beauty-beast.jpg",
        age_group: "7-9",
        reading_level: "intermediate",
        genre: "fairy tale",
        description: "A tale of true love that transforms a beast into a prince!",
        content: "stories/beauty-beast.html"
    },
    {
        id: 32,
        title: "The Boy Who Cried Wolf",
        cover: "images/library/covers/boy-wolf.jpg",
        age_group: "5-7",
        reading_level: "beginner",
        genre: "educational",
        description: "A lesson about honesty and the consequences of lying!",
        content: "stories/boy-wolf.html"
    },
    {
        id: 34,
        title: "The Tortoise and the Hare",
        cover: "images/library/covers/tortoise-hare.avif",
        age_group: "5-7",
        reading_level: "beginner",
        genre: "animals",
        description: "A classic fable about perseverance and the value of steady progress!",
        content: "stories/tortoise-hare.html"
    },
    {
        id: 30,
        title: "Snow White",
        cover: "images/library/covers/snow-white.jpg",
        age_group: "7-9",
        reading_level: "intermediate",
        genre: "fairy tale",
        description: "The classic tale of a princess, seven dwarfs, and a wicked queen!",
        content: "stories/snow-white.html"
    },
    {
        id: 15,
        title: "Emma's Jungle Discovery",
        cover: "images/library/covers/jungle-explorer.webp",
        age_group: "7-9",
        reading_level: "intermediate",
        genre: "adventure",
        description: "Join adventurous Explorer Emma as she discovers hidden temples and ancient mysteries in the jungle!",
        content: "stories/jungle-explorer.html"
    },
    {
        id: 2,
        title: "Captain Luna's Cosmic Quest",
        cover: "images/library/covers/space-adventure.webp",
        age_group: "7-9",
        reading_level: "intermediate",
        genre: "adventure",
        description: "Blast off into space with Captain Luna and her crew as they explore distant planets and make new alien friends!",
        content: "stories/space-adventure.html"
    },
    {
        id: 6,
        title: "Polar Bear's Winter Journey",
        cover: "images/library/covers/arctic-animals.webp",
        age_group: "7-9",
        reading_level: "intermediate",
        genre: "animals",
        description: "Journey to the frozen north and discover how polar animals survive in the coldest place on Earth!",
        content: "stories/arctic-animals.html"
    },
    {
        id: 8,
        title: "World Explorer",
        cover: "images/library/covers/world-cultures.jpeg",
        age_group: "9-11",
        reading_level: "advanced",
        genre: "educational",
        description: "Travel around the world and learn about different cultures, traditions, and celebrations!",
        content: "stories/world-cultures.html"
    },
    {
        id: 10,
        title: "Alex's Alphabet Adventure",
        cover: "images/library/covers/alphabet-adventure.jpg",
        age_group: "5-7",
        reading_level: "beginner",
        genre: "educational",
        description: "Embark on a fun journey through the alphabet with Alex and his animated letter friends!",
        content: "stories/alphabet-adventure.html"
    },
    {
        id: 31,
        title: "Cinderella",
        cover: "images/library/covers/cinderella.jpg",
        age_group: "7-9",
        reading_level: "intermediate",
        genre: "fairy tale",
        description: "A magical story of a kind girl who finds her happily ever after!",
        content: "stories/cinderella.html"
    },
    {
        id: 33,
        title: "The Lion and the Mouse",
        cover: "images/library/covers/lion-mouse.avif",
        age_group: "5-7",
        reading_level: "beginner",
        genre: "animals",
        description: "A story about how even the smallest friends can be the greatest help!",
        content: "stories/lion-mouse.html"
    },
    {
        id: 38,
        title: "We're Going on a Bear Hunt",
        cover: "images/library/covers/bear-hunt.jpg",
        age_group: "5-7",
        reading_level: "beginner",
        genre: "adventure",
        description: "An exciting adventure through different landscapes in search of a bear!",
        content: "stories/bear-hunt.html"
    },
    {
        id: 39,
        title: "Space Explorer",
        cover: "images/library/covers/space-explorer.jpeg",
        age_group: "7-9",
        reading_level: "intermediate",
        genre: "adventure",
        description: "Blast off into space and explore the wonders of the universe!",
        content: "stories/space-explorer.html"
    },
    {
        id: 40,
        title: "Science Squad",
        cover: "images/library/covers/science-squad.jpg",
        age_group: "9-11",
        reading_level: "intermediate",
        genre: "educational",
        description: "Join a team of young scientists as they make amazing discoveries!",
        content: "stories/science-squad.html"
    },
    {
        id: 43,
        title: "Dinosaur Discovery",
        cover: "images/library/covers/dinosaur-discovery.webp",
        age_group: "7-9",
        reading_level: "intermediate",
        genre: "educational",
        description: "Travel back in time to meet the dinosaurs and learn about prehistoric life!",
        content: "stories/dinosaur-discovery.html"
    },
    {
        id: 24,
        title: "The Monkey and the Crocodile",
        cover: "images/library/covers/monkey-crocodile.jpeg",
        age_group: "7-9",
        reading_level: "intermediate",
        genre: "folk tale",
        description: "A clever monkey outsmarts a crocodile in this classic Indian folktale about friendship, trust, and quick thinking!",
        content: "stories/monkey-crocodile.html"
    },
    {
        id: 22,
        title: "Ali Baba and the Forty Thieves",
        cover: "images/library/covers/ali-baba.jpeg",
        age_group: "9-11",
        reading_level: "intermediate",
        genre: "folk tale",
        description: "Follow Ali Baba's adventure as he discovers a secret cave filled with treasure and outsmarts forty thieves with the help of his clever servant Morgiana!",
        content: "stories/ali-baba.html"
    },
    {
        id: 17,
        title: "The Magic Forest",
        cover: "images/library/covers/magic-forest.jpg",
        age_group: "5-7",
        reading_level: "beginner",
        genre: "fantasy",
        description: "Join Lily on a magical adventure in an enchanted forest where trees whisper secrets and magical creatures help her find her way home!",
        content: "stories/magic-forest.html"
    },
    {
        id: 14,
        title: "Rainbow's Magic Garden",
        cover: "images/library/covers/unicorn-garden.webp",
        age_group: "5-7",
        reading_level: "beginner",
        genre: "fantasy",
        description: "Visit the enchanted garden where magical unicorns play and spread joy with their rainbow magic!",
        content: "stories/unicorn-garden.html"
    },
    {
        id: 13,
        title: "The Princess and the Dragon",
        cover: "images/library/covers/princess.jpg",
        age_group: "5-7",
        reading_level: "beginner",
        genre: "fairy tale",
        description: "Experience a classic fairy tale with a modern twist, filled with princesses, dragons, and magical moments!",
        content: "stories/fairy-tale.html"
    },
    {
        id: 11,
        title: "Counting Animals",
        cover: "images/library/covers/counting-animals.jpg",
        age_group: "5-7",
        reading_level: "beginner",
        genre: "educational",
        description: "Learn to count with friendly animals in this interactive mathematical adventure!",
        content: "stories/counting-animals.html"
    },
    {
        id: 7,
        title: "Farmer Joe's Big Day",
        cover: "images/library/covers/farm-animals.jpg",
        age_group: "5-7",
        reading_level: "beginner",
        genre: "animals",
        description: "Visit the friendly farm animals and learn about life on the farm with Farmer Joe!",
        content: "stories/farm-animals.html"
    },
    {
        id: 9,
        title: "Science Squad",
        cover: "images/library/covers/science-experiment.avif",
        age_group: "9-11",
        reading_level: "intermediate",
        genre: "educational",
        description: "Join Professor Spark in her laboratory for exciting experiments and scientific discoveries!",
        content: "stories/science-experiment.html"
    },
    {
        id: 25,
        title: "The Little Mermaid",
        cover: "images/library/covers/little-mermaid.jpg",
        age_group: "7-9",
        reading_level: "intermediate",
        genre: "fairy tale",
        description: "Follow the journey of a young mermaid who dreams of living on land and finding true love!",
        content: "stories/little-mermaid.html"
    },
    {
        id: 26,
        title: "Aladdin and the Magic Lamp",
        cover: "images/library/covers/aladdin.webp",
        age_group: "7-9",
        reading_level: "intermediate",
        genre: "fairy tale",
        description: "Join Aladdin on his magical adventure with a genie who grants three wishes!",
        content: "stories/aladdin.html"
    },
    {
        id: 41,
        title: "World Explorer",
        cover: "images/library/covers/world-explorer.webp",
        age_group: "9-11",
        reading_level: "intermediate",
        genre: "educational",
        description: "Travel around the world and learn about different cultures and places!",
        content: "stories/world-explorer.html"
    }
]; 