import { NestFactory } from '@nestjs/core';
import { AppModule } from './app.module';
import { ValidationPipe } from '@nestjs/common';
import { DocumentBuilder, SwaggerModule } from '@nestjs/swagger'; // 1. Importar Swagger

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

  // 2. Configurar la documentación OpenAPI / Swagger
  const config = new DocumentBuilder()
    .setTitle('Proyecto Ganadero API - NestJS')
    .setDescription('Documentación de los endpoints y contratos del Hito 1')
    .setVersion('1.0')
    .build();
    
  const document = SwaggerModule.createDocument(app, config);
  SwaggerModule.setup('api/docs', app, document); // Estará disponible en /api/docs

  const port = process.env.PORT ?? 3000;
  await app.listen(port);
  console.log(`Aplicación corriendo en: http://localhost:${port}/api/v1/animales`);
  console.log(`Swagger UI disponible en: http://localhost:${port}/api/docs`);
}
bootstrap();