import { motion, AnimatePresence } from 'framer-motion';

/**
 * 1. VARIANTES DE TRANSIÇÃO (CONFIGURAÇÕES)
 */

// Animação do Card de Login ao carregar a página
const containerVariants = {
  hidden: { opacity: 0, scale: 0.9, rotateY: -10 },
  visible: { 
    opacity: 1, 
    scale: 1, 
    rotateY: 0,
    transition: { duration: 0.8, ease: "easeOut", staggerChildren: 0.1 } 
  },
  exit: { 
    opacity: 0, 
    scale: 1.1, 
    transition: { duration: 0.5 } 
  }
};

// Animação de entrada para elementos individuais (labels, inputs, botões)
const itemVariants = {
  hidden: { y: 20, opacity: 0 },
  visible: { y: 0, opacity: 1 }
};

/**
 * 2. ESTRUTURA DA TRANSIÇÃO DE TELA (ORQUESTRAÇÃO)
 */

export default function AnimationWrapper() {
  const [view, setView] = useState('login'); // 'login' ou 'dashboard'
  const [isLoading, setIsLoading] = useState(false);

  return (
    <AnimatePresence mode="wait">
      {view === 'login' ? (
        /* A key é essencial para o Framer Motion saber quando disparar o exit */
        <motion.div key="login" variants={containerVariants} initial="hidden" animate="visible" exit="exit">
          {/* Conteúdo do Login */}
          
          {/* 3. CAMADA DE TRANSIÇÃO (O "PORTAL" VERMELHO) */}
          <AnimatePresence>
            {isLoading && (
              <motion.div 
                initial={{ x: '100%' }} // Começa fora da tela à direita
                animate={{ x: 0 }}      // Desliza para cobrir tudo
                exit={{ opacity: 0 }}   // Desaparece suavemente após a troca
                transition={{ duration: 0.8, ease: [0.87, 0, 0.13, 1] }} // Ease tipo "Expo" para impacto
                className="absolute inset-0 bg-red-600 z-50 flex flex-col items-center justify-center text-white"
              >
                <motion.h1 
                  initial={{ scale: 0.8, opacity: 0 }}
                  animate={{ scale: 1, opacity: 1 }}
                  className="text-8xl font-black"
                >
                  SENAI
                </motion.h1>
              </motion.div>
            )}
          </AnimatePresence>
        </motion.div>
      ) : (
        /* 4. ANIMAÇÃO DE ENTRADA DO DASHBOARD */
        <motion.div 
          key="dashboard"
          initial={{ opacity: 0 }} 
          animate={{ opacity: 1 }}
          transition={{ duration: 0.5 }}
        >
          {/* Elementos internos do dashboard usando itemVariants para efeito de cascata */}
          <motion.div variants={itemVariants}>
             {/* Conteúdo que "sobe" ao aparecer */}
          </motion.div>
        </motion.div>
      )}
    </AnimatePresence>
  );
}

/**
 * 5. ANIMAÇÃO DE MICRO-INTERAÇÃO (BOTÕES E INPUTS)
 */
const ButtonAnimation = () => (
  <motion.button 
    whileHover={{ scale: 1.02 }}
    whileTap={{ scale: 0.98 }}
    className="group"
  >
    <span>ENTRAR</span>
    {/* Ícone que se move no hover */}
    <ChevronRight className="group-hover:translate-x-1 transition-transform" />
  </motion.button>
);

/**
 * 6. ANIMAÇÃO DE BARRAS DE CARREGAMENTO (DASHBOARD)
 */
const ProgressBar = ({ percentage }) => (
  <motion.div 
    initial={{ width: 0 }}
    animate={{ width: `${percentage}%` }}
    transition={{ duration: 1.5, ease: "easeOut" }}
    className="h-full bg-red-600"
  />
);