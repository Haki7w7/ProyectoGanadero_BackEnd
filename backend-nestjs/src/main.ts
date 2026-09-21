import { NestFactory } from '@nestjs/core';
import { AppModule } from './app.module';
import { ValidationPipe } from '@nestjs/common';

async function bootstrap() {
  const app = await NestFactory.create(AppModule);

  // Define el prefijo /api para todas las rutas
  app.setGlobalPrefix('api');

  // Habilita la validación global de DTOs
  app.useGlobalPipes(
    new ValidationPipe({
      whitelist: true,            // Remueve propiedades que no estén en el DTO
      forbidNonWhitelisted: true, // Lanza error si envían propiedades no permitidas
      transform: true,            // Transforma los datos de entrada a los tipos del DTO
      errorHttpStatusCode: 422,   // Retorna código 422 Unprocessable Entity en errores
    }),
  );

  const port = process.env.PORT ?? 3000;
  await app.listen(port);
  console.log(`Aplicación corriendo en: http://localhost:${port}/api/v1/animales`);
}
bootstrap();