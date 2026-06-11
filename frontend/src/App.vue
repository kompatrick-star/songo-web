<template>
  <RouterView />
</template>

<style>
/* CSS GLOBAL : FOND EN ÉCORCE DE BOIS TRADITIONNELLE */
body {
  margin: 0;
  font-family: 'Poppins', sans-serif;
  /* La texture de fond en écorce de bois riche */
  background: #1a100a url('http://googleusercontent.com/image_collection/image_retrieval/7798593512143050459') repeat;
  background-blend-mode: multiply;
  color: #fff3dd;
  overflow-x: hidden;
}

#app {
  width: 100vw;
  min-height: 100vh;
  display: flex;
  justify-content: center;
}
</style>